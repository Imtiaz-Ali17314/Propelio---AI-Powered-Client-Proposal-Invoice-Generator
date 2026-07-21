<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank_transfer,card,other'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Overpayment guard: a payment can never push the invoice's paid total
     * past its balance due. We reject it here instead of silently letting
     * balance_due go negative — keeps invoice math (and the "Paid" status)
     * meaningful. Route-model-bound 'invoice' comes from the wrapping
     * Route::apiResource / nested route param.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $invoice = $this->route('invoice');

            if (! $invoice || ! $this->filled('amount')) {
                return;
            }

            $amount = (float) $this->input('amount');
            $balanceDue = $invoice->balance_due;

            if ($amount > $balanceDue) {
                $validator->errors()->add(
                    'amount',
                    $balanceDue > 0
                        ? "Payment amount cannot exceed the remaining balance due (\${$this->formatMoney($balanceDue)})."
                        : 'This invoice is already fully paid.',
                );
            }
        });
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2);
    }
}