<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    /**
     * List customers with search.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the customer creation form.
     */
    public function create()
    {
        $this->authorize('create', Customer::class);

        return view('customers.create');
    }

    /**
     * Store a new customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('customers.show', $customer)->with('status', 'Customer created.');
    }

    /**
     * Show a customer with their purchase history, debts and total spent.
     */
    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $sales = $customer->sales()->latest()->take(10)->get();
        $debts = $customer->debts()->latest()->take(10)->get();
        $totalSpent = (float) $customer->sales()->where('status', 'completed')->sum('total');

        return view('customers.show', compact('customer', 'sales', 'debts', 'totalSpent'));
    }

    /**
     * Show the customer edit form.
     */
    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update a customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated.');
    }

    /**
     * Soft-delete a customer (admin only).
     */
    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'Customer deleted.');
    }

    /**
     * Search customers by name/phone for the AJAX searchable select.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $customers = Customer::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }
}
