<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderCategoryRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with(['children.children'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $maxOrder = Category::where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') ?? 0;

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('categories', 'public');
            $imageUrl = '/storage/' . $path;
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'image_url' => $imageUrl,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', "Category '{$validated['name']}' created.");
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('categories', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        $category->update($validated);

        return back()->with('success', 'Category updated.');
    }

    public function reorder(ReorderCategoryRequest $request)
    {
        $validated = $request->validated();

        foreach ($validated['items'] as $item) {
            Category::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Category $category)
    {
        // Move children to root if any
        $category->children()->update(['parent_id' => null]);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
