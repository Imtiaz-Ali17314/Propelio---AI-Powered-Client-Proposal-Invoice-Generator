<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'issued_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issued_date'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:unpaid,partially_paid,paid,overdue,cancelled'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}