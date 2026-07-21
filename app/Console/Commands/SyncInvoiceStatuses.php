<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class SyncInvoiceStatuses extends Command
{
    protected $signature = 'invoices:sync-statuses';

    protected $description = 'Recompute invoice statuses (e.g. mark overdue invoices) based on due dates and payments.';

    public function handle(): int
    {
        // Only invoices in "live" states need checking — draft/cancelled/paid
        // are either not yet active or already in a final state.
        $invoices = Invoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->get();

        $changed = 0;

        foreach ($invoices as $invoice) {
            $before = $invoice->status;
            $invoice->syncStatus();

            if ($invoice->status !== $before) {
                $changed++;
            }
        }

        $this->info("Checked {$invoices->count()} invoice(s), updated {$changed} status(es).");

        return self::SUCCESS;
    }
}