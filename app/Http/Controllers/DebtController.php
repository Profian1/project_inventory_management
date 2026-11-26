<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebtController extends Controller
{
    public function __construct(private DebtService $debtService)
    {
    }

    /**
     * List debts with filters and summary cards.
     */
    public function index(Request $request)
    {
        $debts = Debt::query()
            ->with(['customer', 'sale'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('overdue'), fn ($q) => $q->whereNotNull('due_date')->where('due_date', '<', today()))
            ->orderByDesc('balance')
            ->paginate(20)
            ->withQueryString();

        return view('debts.index', [
            'debts' => $debts,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'totalOwed' => (float) Debt::unpaid()->sum('balance'),
            'overdueAmount' => (float) Debt::unpaid()->whereNotNull('due_date')->where('due_date', '<', today())->sum('balance'),
            'debtorsCount' => Debt::unpaid()->distinct()->count('customer_id'),
            'collectedThisMonth' => (float) DebtPayment::whereBetween('created_at', [now()->startOfMonth(), now()])->sum('amount'),
        ]);
    }

    /**
     * Show a single debt with its payment history.
     */
    public function show(Debt $debt)
    {
        $debt->load(['customer', 'sale', 'payments' => fn ($q) => $q->latest()->with('user')]);

        return view('debts.show', compact('debt'));
    }

    /**
     * Record a payment against a debt.
     */
    public function storePayment(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,mpesa'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->debtService->recordPayment(
                $debt,
                (float) $validated['amount'],
                $validated['payment_method'],
                $validated['notes'] ?? null,
                $request->user()->id,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('debts.show', $debt)->with('status', 'Payment recorded.');
    }

    /**
     * Debtors aging report grouped by customer.
     */
    public function aging()
    {
        return view('debts.aging', ['report' => $this->agingReport()]);
    }

    /**
     * Export the aging report as CSV.
     */
    public function exportAging(): StreamedResponse
    {
        $report = $this->agingReport();
        $filename = 'debtors-aging-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Customer', 'Current (0-30)', '31-60 days', '61-90 days', '90+ days', 'Total']);

            foreach ($report as $row) {
                fputcsv($handle, [
                    $row['customer'],
                    $row['current'],
                    $row['d31_60'],
                    $row['d61_90'],
                    $row['d90_plus'],
                    $row['total'],
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    /**
     * Build the aging report: each customer's balance split into age buckets.
     */
    private function agingReport(): array
    {
        $debts = Debt::unpaid()->with('customer')->get();

        $report = [];

        foreach ($debts as $debt) {
            $customerId = $debt->customer_id;

            if (! isset($report[$customerId])) {
                $report[$customerId] = [
                    'customer' => $debt->customer?->name ?? 'Unknown',
                    'current' => 0,
                    'd31_60' => 0,
                    'd61_90' => 0,
                    'd90_plus' => 0,
                    'total' => 0,
                ];
            }

            $age = $debt->created_at->diffInDays(now());
            $bucket = $age <= 30 ? 'current' : ($age <= 60 ? 'd31_60' : ($age <= 90 ? 'd61_90' : 'd90_plus'));

            $report[$customerId][$bucket] += (float) $debt->balance;
            $report[$customerId]['total'] += (float) $debt->balance;
        }

        return $report;
    }
}
