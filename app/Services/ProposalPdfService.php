<?php

namespace App\Services;

use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ProposalPdfService
{
    public function stream(Proposal $proposal): Response
    {
        $pdf = Pdf::loadView('pdf.proposal', [
            'proposal' => $proposal,
        ])->setPaper('a4');

        $filename = "Proposal-" . str_replace(' ', '-', $proposal->title) . ".pdf";

        return $pdf->stream($filename);
    }
}
