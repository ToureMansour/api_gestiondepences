<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('expenses')
            ->withSum('expenses', 'amount')
            ->get()
            ->map(function ($category) {
                $category->total_amount = number_format($category->expenses_sum_amount ?? 0, 2, '.', '');
                unset($category->expenses_sum_amount);
                return $category;
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categorie supprimee'
        ]);
    }
}
