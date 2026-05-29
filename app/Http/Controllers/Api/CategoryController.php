<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('resources')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:7',
            'active'      => 'boolean',
        ]);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('resources'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:7',
            'active'      => 'boolean',
        ]);

        $category->update($data);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
