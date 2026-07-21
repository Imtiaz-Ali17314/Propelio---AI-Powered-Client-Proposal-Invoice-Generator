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
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
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
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
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

        $lineItems = $proposal->cost_breakdown['items'] ?? [];
        $taxPercent = $proposal->cost_breakdown['tax_percent'] ?? 0;

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
                $invoice->items()->create([
                    'description' => $item['label'] ?? 'Item',
                    'quantity' => 1,
                    'rate' => $item['amount'] ?? 0,
                ]);
            }

            // Carry over the tax_percent the AI proposal used, so totals line up.
            $invoice->recalculateTotals($taxPercent);

            return $invoice;
        });

        $invoice->load(['client', 'items']);

        return response()->json($invoice, 201);
    }

    public function downloadPdf(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        return $pdfService->stream($invoice);
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        // Find the highest existing number this year and increment from there —
        // counting rows breaks once any invoice in between gets deleted.
        $lastNumber = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextSequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, strlen($prefix));
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