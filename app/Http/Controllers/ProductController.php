<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $query = Product::query();

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('category', 'like', '%' . $request->search . '%');
            }

            if ($request->has('sort') && $request->has('direction')) {
                $query->orderBy($request->sort, $request->direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $product = $query->paginate($limit);
            return response()->json([
                'message' => 'Successfully fetched product data',
                'status' => 'Success',
                'data' => $product->items(), 
                'pagination' => [
                    'current_page' => $product->currentPage(),
                    'per_page' => $product->perPage(),
                    'total' => $product->total(),
                    'last_page' => $product->lastPage(),
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'category' => 'required|string',
                'price' => 'required|integer'
            ]);
            $validated['active'] = true;
            $validated['product_code'] = 'product_' . (Product::max('id') + 1);

            $product = Product::create($validated); 
            return response()->json([
                'message' => 'Product successfully created',
                'status' => 'Success',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $product_code)
    {
        try {
            $product = Product::where('product_code', $product_code)->firstOrFail();
            return response()->json([
                'message' => 'Product fetched successfully',
                'status' => 'Success',
                'data' => $product,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => 'The product with the specified code does not exist.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $product_code)
    {
        try {
            $product = Product::where('product_code', $product_code)->firstOrFail();

            $validated = $request->validate([
                'name' => 'nullable|string',
                'category' => 'nullable|string',
                'price' => 'nullable|integer',
            ]);

            $product->update($validated);

            return response()->json([
                'message' => 'Product successfully updated',
                'status' => 'Success',
                'data' => $product,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => 'The product with the specified code does not exist.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $product_code)
    {
        try {
            $product = Product::where('product_code', $product_code)->firstOrFail();
            $product->delete();

            return response()->json([
                'message' => 'Product successfully deleted',
                'status' => 'Success',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => 'The product with the specified code does not exist.'
            ], 404);
        }
    }
}
