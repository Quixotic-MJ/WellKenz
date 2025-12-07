<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Category::with(['items' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 10);
        $categories = $query->paginate($perPage)
            ->withQueryString()
            ->through(function ($category) {
                $category->linked_items_count = $category->items->count();
                return $category;
            });

        return view('Admin.master_files.categories', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'icon' => $request->icon ?? 'fas fa-tag',
                'color' => $request->color ?? '#8B4513',
                'is_active' => true
            ]);



            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified category in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            $oldData = $category->toArray();
            
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'icon' => $request->icon ?? 'fas fa-tag',
                'color' => $request->color ?? '#8B4513',
            ]);



            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has items
            if ($category->items()->where('is_active', true)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with active items. Please reassign or delete associated items first.'
                ], 422);
            }

            $categoryName = $category->name;
            $category->delete();



            return response()->json([
                'success' => true,
                'message' => "Category '{$categoryName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category active/inactive status.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Category $category)
    {
        try {
            $oldStatus = $category->is_active;
            $category->is_active = !$category->is_active;
            $category->save();

            $status = $category->is_active ? 'activated' : 'deactivated';



            return response()->json([
                'success' => true,
                'message' => "Category {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent categories for dropdown.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getParentCategories(Request $request)
    {
        $excludeId = $request->get('exclude_id');
        
        $query = Category::where('is_active', true)
            ->orderBy('name');
            
        // Exclude the current category being edited to prevent circular references
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $categories = $query->get(['id', 'name']);

        return response()->json($categories);
    }

    /**
     * Search categories for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'description']);

        return response()->json($categories);
    }
}