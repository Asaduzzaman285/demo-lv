<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopifyProductRequest;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function store(StoreShopifyProductRequest $request): JsonResponse
    {
        $shopUrl = $request->header('X-Shopify-Shop-Domain');
        $accessToken = $request->header('X-Shopify-Access-Token');

        if (!$shopUrl || !$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Shopify shop domain or access token'
            ], 400);
        }

        // Get location ID from request or use default from config
        $locationId = $request->input('location_id') ?? config('shopify.location_id');

        $result = $this->productRepository->createProductWithVariations(
            $request->validated(),
            $shopUrl,
            $accessToken,
            $locationId
        );

        $statusCode = $result['success'] ? 201 : (isset($result['errors']) ? 422 : 500);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['product'] ?? null,
            'errors' => $result['errors'] ?? $result['error'] ?? null
        ], $statusCode);
    }
}
