<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Check if the loyalty program is enabled.
     */
    public function isEnabled(): bool
    {
        return SystemSetting::get('loyalty_enabled', '0') === '1';
    }

    /**
     * Calculate how many points a given purchase amount earns.
     * Uses admin-configured rate: loyalty_birr_per_point (how many birr = 1 point)
     * Amount is in minor units (cents).
     */
    public function calculatePoints(int $amountCents): int
    {
        // Admin sets birr_per_point as birr value (e.g., 100 means every 100 birr = 1 point)
        $birrPerPoint = (int) SystemSetting::get('loyalty_birr_per_point', '100');
        if ($birrPerPoint <= 0) {
            return 0;
        }
        
        // Convert cents to birr, then divide by the rate
        $amountInBirr = $amountCents / 100;
        return (int) floor($amountInBirr / $birrPerPoint);
    }

    /**
     * Award points to a user for a purchase.
     */
    public function earnPoints(
        User $user,
        int $purchaseAmountCents,
        string $source, // 'online_order' or 'in_shop'
        ?int $referenceId = null,
        ?string $referenceType = null,
        ?int $staffId = null,
        ?string $description = null
    ): ?LoyaltyTransaction {
        if (!$this->isEnabled()) {
            return null;
        }
        
        $points = $this->calculatePoints($purchaseAmountCents);
        if ($points <= 0) {
            return null;
        }
        
        return DB::transaction(function () use ($user, $points, $purchaseAmountCents, $source, $referenceId, $referenceType, $staffId, $description) {
            // Get or create loyalty points record
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'lifetime_earned' => 0, 'lifetime_redeemed' => 0]
            );
            
            $loyaltyPoint->increment('balance', $points);
            $loyaltyPoint->increment('lifetime_earned', $points);
            
            return LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => 'earn',
                'points' => $points,
                'source' => $source,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'purchase_amount_cents' => $purchaseAmountCents,
                'description' => $description ?? "Earned {$points} points from purchase",
                'staff_id' => $staffId,
            ]);
        });
    }

    /**
     * Redeem points for a discount. Returns the discount in cents.
     */
    public function redeemPoints(User $user, int $pointsToRedeem): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }
        
        $pointValueCents = (int) SystemSetting::get('loyalty_point_value_cents', '100');
        $minRedeem = (int) SystemSetting::get('loyalty_min_redeem', '50');
        
        if ($pointsToRedeem < $minRedeem) {
            return 0;
        }
        
        $loyaltyPoint = LoyaltyPoint::where('user_id', $user->id)->first();
        if (!$loyaltyPoint || $loyaltyPoint->balance < $pointsToRedeem) {
            return 0;
        }
        
        $discountCents = $pointsToRedeem * $pointValueCents;
        
        DB::transaction(function () use ($loyaltyPoint, $user, $pointsToRedeem, $discountCents) {
            $loyaltyPoint->decrement('balance', $pointsToRedeem);
            $loyaltyPoint->increment('lifetime_redeemed', $pointsToRedeem);
            
            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => 'redeem',
                'points' => -$pointsToRedeem,
                'source' => 'redemption',
                'description' => "Redeemed {$pointsToRedeem} points for " . number_format($discountCents / 100, 2) . " Birr discount",
            ]);
        });
        
        return $discountCents;
    }

    /**
     * Get user's current points balance.
     */
    public function getBalance(User $user): int
    {
        return LoyaltyPoint::where('user_id', $user->id)->value('balance') ?? 0;
    }

    /**
     * Get transaction history for a user.
     */
    public function getHistory(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return LoyaltyTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
    
    /**
     * Get loyalty program settings for display.
     */
    public function getSettings(): array
    {
        return [
            'enabled' => SystemSetting::get('loyalty_enabled', '0') === '1',
            'birr_per_point' => (int) SystemSetting::get('loyalty_birr_per_point', '100'),
            'point_value_cents' => (int) SystemSetting::get('loyalty_point_value_cents', '100'),
            'min_redeem' => (int) SystemSetting::get('loyalty_min_redeem', '50'),
        ];
    }
}
