<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return response()->json(
            $invoice->payments()->latest('paid_at')->get()
        );
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        // Recording a payment is an update to the invoice's financial state.
        $this->authorize('update', $invoice);

        $payment = $invoice->payments()->create($request->validated());

        // Payment changes the invoice's paid amount — status must re-sync immediately.
        $invoice->syncStatus();

        $invoice->load(['client', 'items', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return response()->json([
            ...$invoice->toArray(),
            'paid_amount' => $invoice->paidAmount,
            'balance_due' => $invoice->balanceDue,
            'last_payment' => $payment,
        ], 201);
    }

    public function destroy(Invoice $invoice, Payment $payment)
    {
        $this->authorize('update', $invoice);

        // Guard against cross-invoice deletion via mismatched route params.
        abort_if($payment->invoice_id !== $invoice->id, 404);

        $payment->delete();

        // Removing a payment can drop status back down (paid -> partially_paid -> unpaid).
        $invoice->syncStatus();

        return response()->json(['message' => 'Payment removed.']);
    }
}