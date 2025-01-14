<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $query = Customer::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort') && $request->has('direction')) {
            $query->orderBy($request->sort, $request->direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $customers = $query->paginate($limit);

        return response()->json([
            'message' => 'Successfully fetched customer data',
            'status' => 'Success',
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string',
                'address' => 'required|string',
                'gender' => 'required|string',
            ]);

            $validated['active'] = true;
            $validated['id_customer'] = 'customer_' . Customer::max('id') + 1; 


            $customer = Customer::create($validated);

            return response()->json([
                'message' => 'Customer successfully created',
                'status' => 'Success',
                'data' => $customer,
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
    public function show(string $id_customer)
    {
        try {
            $customer = Customer::where('id_customer', $id_customer)->firstOrFail();

            return response()->json([
                'message' => 'Customer successfully found',
                'status' => 'Success',
                'data' => $customer,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer not found',
                'message' => 'The customer with the specified ID does not exist.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id_customer)
    {
        try {
            $customer = Customer::where('id_customer', $id_customer)->firstOrFail();

            $validated = $request->validate([
                'name' => 'nullable|string',
                'address' => 'nullable|string',
                'gender' => 'nullable|string|in:pria,wanita',
            ]);

            $customer->update($validated);

            return response()->json([
                'message' => 'Customer successfully updated',
                'status' => 'Success',
                'data' => $customer,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer not found',
                'message' => 'The customer with the specified ID does not exist.',
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
    public function destroy(string $id_customer)
    {
        try {
            $customer = Customer::where('id_customer', $id_customer)->firstOrFail();
            $customer->delete();

            return response()->json([
                'message' => 'Customer deleted successfully',
                'status' => 'Success',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer not found',
                'message' => 'The customer with the specified ID does not exist.',
            ], 404);
        }
    }

    public function getSimpleList(Request $request)
    {
        try {
            $query = Customer::query();

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $customers = $query->select('id', 'name','id_customer')->get();

            return response()->json([
                'message' => 'Successfully fetched simplified customer data',
                'status' => 'Success',
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    
}
