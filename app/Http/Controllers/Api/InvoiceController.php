<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = $request->user()
            ->invoices()
            ->with('client')
            ->latest()
            ->get()
            ->each(fn (Invoice $invoice) => $invoice->syncStatus());

        return response()->json($invoices);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->syncStatus();
        $invoice->load(['client', 'items', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return response()->json([
            ...$invoice->toArray(),
            'paid_amount' => $invoice->paidAmount,
            'balance_due' => $invoice->balanceDue,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $invoice = DB::transaction(function () use ($request) {
            $invoice = $request->user()->invoices()->create([
                'client_id' => $request->client_id,
                'proposal_id' => $request->proposal_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'unpaid',
                'issued_date' => $request->issued_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $rawRate = $item['rate'] ?? 0;
                $cleanRate = is_numeric($rawRate)
                    ? (float) $rawRate
                    : (float) preg_replace('/[^0-9.]/', '', (string) $rawRate);

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'rate' => $cleanRate,
                ]);
            }

            $invoice->recalculateTotals($request->tax_percent ?? 0);

            return $invoice;
        });

        $invoice->load(['client', 'items']);

        return response()->json($invoice, 201);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'client_id' => $request->client_id,
                'issued_date' => $request->issued_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Replace items wholesale — simplest correct approach for a form-based edit.
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                $rawRate = $item['rate'] ?? 0;
                $cleanRate = is_numeric($rawRate)
                    ? (float) $rawRate
                    : (float) preg_replace('/[^0-9.]/', '', (string) $rawRate);

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'rate' => $cleanRate,
                ]);
            }

            $invoice->recalculateTotals($request->tax_percent ?? 0);

            // Manual status override only allowed for 'cancelled'; everything
            // else is derived by syncStatus() so it can't drift from reality.
            if ($request->status === 'cancelled') {
                $invoice->update(['status' => 'cancelled']);
            } else {
                $invoice->syncStatus();
            }
        });

        $invoice->load(['client', 'items']);

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted.']);
    }

    /**
     * Convert a Proposal's cost_breakdown into a new draft Invoice.
     */
    public function storeFromProposal(Request $request, Proposal $proposal)
    {
        $this->authorize('view', $proposal);
        $this->authorize('create', Invoice::class);

        $costBreakdown = $proposal->cost_breakdown;
        if (is_string($costBreakdown)) {
            $costBreakdown = json_decode($costBreakdown, true) ?? [];
        }

        $lineItems = is_array($costBreakdown) ? ($costBreakdown['items'] ?? []) : [];
        $taxPercent = is_array($costBreakdown) ? (float) ($costBreakdown['tax_percent'] ?? 0) : 0;

        // Fallback if no specific line items are present
        if (empty($lineItems)) {
            $lineItems = [
                [
                    'label' => $proposal->title ?: 'Proposal Service',
                    'amount' => $proposal->total_amount ?? 0,
                ],
            ];
        }

        try {
            $invoice = DB::transaction(function () use ($request, $proposal, $lineItems, $taxPercent) {
                $invoice = $request->user()->invoices()->create([
                    'client_id' => $proposal->client_id,
                    'proposal_id' => $proposal->id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'status' => 'unpaid',
                    'issued_date' => now()->toDateString(),
                    'due_date' => now()->addDays(14)->toDateString(),
                ]);

                foreach ($lineItems as $item) {
                    $rawRate = $item['amount'] ?? $item['rate'] ?? $item['price'] ?? 0;
                    $cleanRate = is_numeric($rawRate)
                        ? (float) $rawRate
                        : (float) preg_replace('/[^0-9.]/', '', (string) $rawRate);

                    $description = $item['label'] ?? $item['name'] ?? $item['description'] ?? 'Item';

                    $invoice->items()->create([
                        'description' => (string) $description,
                        'quantity' => 1,
                        'rate' => $cleanRate,
                    ]);
                }

                // Carry over the tax_percent the AI proposal used, so totals line up.
                $invoice->recalculateTotals($taxPercent);

                return $invoice;
            });

            $invoice->load(['client', 'items']);

            return response()->json($invoice, 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to convert proposal to invoice', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to convert proposal to invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadPdf(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        return $pdfService->stream($invoice);
    }

    public function toggleCancel(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'cancelled') {
            $invoice->status = 'unpaid';
            $invoice->syncStatus();
            $message = 'Invoice reactivated successfully.';
        } else {
            $invoice->update(['status' => 'cancelled']);
            $message = 'Invoice cancelled successfully.';
        }

        $invoice->load(['client', 'items', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return response()->json([
            ...$invoice->toArray(),
            'paid_amount' => $invoice->paidAmount,
            'balance_due' => $invoice->balanceDue,
            'message' => $message,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        // Find the highest existing invoice by ID for safety
        $lastNumber = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $nextSequence = 1;

        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $lastSequence = (int) end($parts);
            $nextSequence = $lastSequence + 1;
        }

        $invoiceNumber = $prefix . sprintf('%03d', $nextSequence);

        // Safety net against any rare race condition.
        while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            $nextSequence++;
            $invoiceNumber = $prefix . sprintf('%03d', $nextSequence);
        }

        return $invoiceNumber;
    }
}