<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->boolean('available_only'), fn ($q) => $q->where('is_available', true))
            ->orderBy('name');

        $products = $query->paginate(min($request->integer('limit', 10), 100));

        return $this->success('Produk berhasil diambil', ProductResource::collection($products), 200, [
            'page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success('Detail produk berhasil diambil', new ProductResource($product->load('category')));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($this->payload($request->validated()));

        return $this->success('Produk berhasil dibuat', new ProductResource($product->load('category')), 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($this->payload($request->validated()));

        return $this->success('Produk berhasil diperbarui', new ProductResource($product->fresh('category')));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success('Produk berhasil dihapus', null);
    }

    /**
     * Kontrak memakai product_name, kolom tabel memakai name.
     */
    private function payload(array $validated): array
    {
        if (isset($validated['product_name'])) {
            $validated['name'] = $validated['product_name'];
            unset($validated['product_name']);
        }

        return $validated;
    }
}
