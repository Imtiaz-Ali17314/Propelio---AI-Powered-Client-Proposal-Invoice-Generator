<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     *
     * Single aggregated endpoint for the dashboard: stat cards, a 6-month
     * revenue trend, and recent activity. Bundled into one call so the
     * frontend doesn't have to fire off 4-5 separate requests on load.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Keep invoice statuses fresh (same pattern as InvoiceController::index)
        // before we aggregate anything off of them.
        $user->invoices()->get()->each->syncStatus();

        return response()->json([
            'stats' => $this->buildStats($user),
            'revenue_chart' => $this->buildRevenueChart($user),
            'recent_proposals' => $this->buildRecentProposals($user),
            'recent_invoices' => $this->buildRecentInvoices($user),
        ]);
    }

    /**
     * Top stat cards: total revenue (paid), pending/outstanding amount,
     * proposals count (by status), invoices count (by status).
     */
    private function buildStats($user): array
    {
        $invoices = $user->invoices()->with('payments')->get();

        $totalRevenue = $invoices->sum(fn ($invoice) => $invoice->paid_amount);
        $pendingAmount = $invoices
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum(fn ($invoice) => $invoice->balance_due);

        $proposalsByStatus = $user->proposals()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $invoicesByStatus = $invoices->groupBy('status')->map->count();

        return [
            'total_revenue' => round($totalRevenue, 2),
            'pending_amount' => round($pendingAmount, 2),
            'proposals' => [
                'total' => $proposalsByStatus->sum(),
                'draft' => (int) ($proposalsByStatus['draft'] ?? 0),
                'sent' => (int) ($proposalsByStatus['sent'] ?? 0),
                'accepted' => (int) ($proposalsByStatus['accepted'] ?? 0),
                'rejected' => (int) ($proposalsByStatus['rejected'] ?? 0),
            ],
            'invoices' => [
                'total' => $invoices->count(),
                'unpaid' => (int) ($invoicesByStatus['unpaid'] ?? 0),
                'partially_paid' => (int) ($invoicesByStatus['partially_paid'] ?? 0),
                'paid' => (int) ($invoicesByStatus['paid'] ?? 0),
                'overdue' => (int) ($invoicesByStatus['overdue'] ?? 0),
                'cancelled' => (int) ($invoicesByStatus['cancelled'] ?? 0),
            ],
        ];
    }

    /**
     * Revenue collected per month for the last 6 months (based on payments,
     * not invoice totals — this is actual cash collected, not billed amount).
     */
    private function buildRevenueChart($user): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $payments = DB::table('payments')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.user_id', $user->id)
            ->where('payments.paid_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(payments.paid_at, '%Y-%m') as month, SUM(payments.amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');

            $months[] = [
                'label' => $date->format('M Y'),
                'total' => round((float) ($payments[$key] ?? 0), 2),
            ];
        }

        return $months;
    }

    private function buildRecentProposals($user)
    {
        return $user->proposals()
            ->with('client:id,name')
            ->latest()
            ->take(5)
            ->get(['id', 'client_id', 'title', 'status', 'total_amount', 'created_at']);
    }

    private function buildRecentInvoices($user)
    {
        return $user->invoices()
            ->with('client:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client' => $invoice->client,
                'status' => $invoice->status,
                'total' => $invoice->total,
                'balance_due' => $invoice->balance_due,
                'created_at' => $invoice->created_at,
            ]);
    }
}
