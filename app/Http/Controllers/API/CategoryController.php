<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return $this->success('Kategori berhasil diambil', CategoryResource::collection($categories));
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return $this->success('Detail kategori berhasil diambil', new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->success('Kategori berhasil dibuat', new CategoryResource($category), 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return $this->success('Kategori berhasil diperbarui', new CategoryResource($category->fresh()));
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return $this->error('Kategori masih punya produk, tidak bisa dihapus', ['category' => 'hapus atau pindahkan produk dulu'], 409);
        }

        $category->delete();

        return $this->success('Kategori berhasil dihapus', null);
    }
}
