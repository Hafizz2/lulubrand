<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageService
{
    protected string $apiKey;
    protected ?string $senderId;
    protected ?string $identifier;
    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.afromessage.api_key', '');
        $this->senderId = config('services.afromessage.sender_id');
        $this->identifier = config('services.afromessage.identifier');
        $this->endpoint = config('services.afromessage.endpoint', 'https://api.afromessage.com/api/send');
    }

    /**
     * Send an SMS message to a phone number.
     *
     * @param string $to Phone number (e.g., 0912345678 or +251912345678)
     * @param string $message Text content of the SMS
     * @return bool
     */
    public function sendSms(string $to, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning("AfroMessage API key is not configured in .env. SMS notification skipped.", [
                'to' => $to,
                'message' => $message,
            ]);
            return false;
        }

        // Clean phone number format (2519XXXXXXXX)
        $cleanPhone = $this->formatPhoneNumber($to);
        if (empty($cleanPhone)) {
            Log::error("Invalid recipient phone number for AfroMessage SMS", ['to' => $to]);
            return false;
        }

        try {
            $queryParams = [
                'to' => $cleanPhone,
                'message' => $message,
            ];

            if (!empty($this->senderId)) {
                $queryParams['sender'] = $this->senderId;
            }

            if (!empty($this->identifier)) {
                $queryParams['from'] = $this->identifier;
            }

            // GET request is standard for AfroMessage send API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ])->timeout(10)->get($this->endpoint, $queryParams);

            $data = $response->json();
            $isAcknowledged = isset($data['acknowledge']) && $data['acknowledge'] === 'success';

            if ($response->successful() && $isAcknowledged) {
                Log::info("AfroMessage SMS sent successfully to {$cleanPhone}", ['response' => $data]);
                return true;
            }

            $errorMsg = isset($data['response']['errors'][0]) ? $data['response']['errors'][0] : 'AfroMessage returned error status';
            Log::error("AfroMessage SMS API error: {$errorMsg}", [
                'status' => $response->status(),
                'body'   => $response->body(),
                'to'     => $cleanPhone,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error("Exception thrown when sending AfroMessage SMS", [
                'error' => $e->getMessage(),
                'to'    => $cleanPhone,
            ]);
            return false;
        }
    }

    /**
     * Standardize Ethiopian phone number formats to 251XXXXXXXXX as required by AfroMessage.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^\d]/', '', trim($phone));
        if (str_starts_with($phone, '09') || str_starts_with($phone, '07')) {
            return '251' . substr($phone, 1);
        }
        if (str_starts_with($phone, '9') || str_starts_with($phone, '7')) {
            return '251' . $phone;
        }
        return $phone;
    }
}
