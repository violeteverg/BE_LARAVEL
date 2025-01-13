<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductTransaction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $limit = $request->query('limit', 10);

            $query = Transaction::query()
                ->select(['id', 'bill_id', 'date', 'subtotal', 'customer_id'])
                ->with([
                    'customer:id,id_customer,name', 
                    'productTransactions:id,transaction_id,product_id,quantity',
                    'productTransactions.product:id,name,product_code,category' 
                ]);
            if ($request->has('search')) {
                $query->where('bill_id', 'like', '%' . $request->search . '%');
            }

            if ($request->has('sort') && $request->has('direction')) {
                $query->orderBy($request->sort, $request->direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $transactions = $query->paginate($limit);

            return response()->json([
                'message' => 'Successfully fetched transaction data',
                'status' => 'Success',
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching data',
                'message' => $e->getMessage(),
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
                'date' => 'required|date',
                'customer_id' => 'required|exists:customers,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            $subtotal = 0;
            foreach ($validated['products'] as $productData) {

                $product = Product::findOrFail($productData['product_id']);

                $subtotal += $product->price * $productData['quantity'];
            }


            $validated['bill_id'] = 'bill_' . Transaction::max('id') + 1; 
            $validated['subtotal'] = $subtotal;

            $transaction = Transaction::create($validated);

            foreach ($validated['products'] as $productData) {
                ProductTransaction::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                ]);
            }

            return response()->json([
                'message' => 'Transaction successfully created',
                'status' => 'Success',
                'data' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $transaction = Transaction::with(['customer', 'productTransactions.product'])->findOrFail($id);
            return response()->json([
                'message' => 'Successfully fetched transaction',
                'status' => 'Success',
                'data' => $transaction,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Transaction not found',
                'message' => 'The transaction with the specified ID does not exist.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            $validated = $request->validate([
                'bill_id' => 'nullable|string|unique:transactions,bill_id,' . $id,
                'date' => 'nullable|date',
                'subtotal' => 'nullable|integer',
                'customer_id' => 'nullable|exists:customers,id',
                'products' => 'nullable|array',
                'products.*.product_id' => 'nullable|exists:products,id',
                'products.*.quantity' => 'nullable|integer|min:1',
            ]);

            $transaction->update($validated);

            if (isset($validated['products'])) {
                foreach ($validated['products'] as $productData) {
                    ProductTransaction::updateOrCreate(
                        [
                            'transaction_id' => $transaction->id,
                            'product_id' => $productData['product_id'],
                        ],
                        ['quantity' => $productData['quantity']]
                    );
                }
            }

            return response()->json([
                'message' => 'Transaction successfully updated',
                'status' => 'Success',
                'data' => $transaction,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Transaction not found',
                'message' => 'The transaction with the specified ID does not exist.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            ProductTransaction::where('transaction_id', $id)->delete();

            $transaction->delete();

            return response()->json([
                'message' => 'Transaction successfully deleted',
                'status' => 'Success',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Transaction not found',
                'message' => 'The transaction with the specified ID does not exist.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
