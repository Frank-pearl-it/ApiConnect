<?php

namespace App\Http\Controllers;

use App\Services\Pax8Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Pax8Controller extends Controller
{
    protected Pax8Service $pax8;

    public function __construct(Pax8Service $pax8)
    {
        $this->pax8 = $pax8;
    }

    /**
     * GET /api/pax8/products
     * Fetch from local DB (hourly refreshed + webhook updated)
     */
    public function listProducts(Request $request)
    {
        try {
            $data = $this->pax8->listProducts($request->query());
            return response()->json([
                'message' => 'Pax8 products retrieved successfully (cached DB)',
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching Pax8 products', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal error while fetching products'], 500);
        }
    }

    /**
     * GET /api/pax8/products/{productId}
     * Fetch single product from local DB
     */
    public function getProduct(string $productId)
    {
        try {
            $data = $this->pax8->getProduct($productId);
            if (!$data) {
                return response()->json(['message' => 'Pax8 product not found'], 404);
            }

            return response()->json([
                'message' => 'Pax8 product retrieved successfully',
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching Pax8 product', [
                'productId' => $productId,
                'error'     => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Internal error while fetching product'], 500);
        }
    }

    /**
     * GET /api/pax8/products/{productId}/pricing
     * (Still fetches live pricing)
     */
    public function getProductPricing(string $productId, Request $request)
    {
        try {
            $data = $this->pax8->getProductPricing($productId, $request->query());
            if (!$data) {
                return response()->json(['message' => 'No pricing found for this product'], 404);
            }

            return response()->json([
                'message' => 'Pax8 product pricing retrieved successfully',
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching Pax8 pricing', [
                'productId' => $productId,
                'error'     => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Internal error while fetching pricing'], 500);
        }
    }

    /**
     * GET /api/pax8/products/{productId}/dependencies
     * (Still fetches live dependencies)
     */
    public function getProductDependencies(string $productId)
    {
        try {
            $data = $this->pax8->getProductDependencies($productId);
            if (!$data) {
                return response()->json(['message' => 'No dependencies found for this product'], 404);
            }

            return response()->json([
                'message' => 'Pax8 product dependencies retrieved successfully',
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching Pax8 product dependencies', [
                'productId' => $productId,
                'error'     => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Internal error while fetching dependencies'], 500);
        }
    }
}
