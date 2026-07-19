<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePdfService
{
    public function stream(Invoice $invoice): StreamedResponse
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        $filename = "{$invoice->invoice_number}.pdf";

        return $pdf->stream($filename);
    }
}