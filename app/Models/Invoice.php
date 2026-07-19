<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'proposal_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'issued_date',
        'due_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_date' => 'date',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Derived money helpers ──────────────────────────────
    // These are NOT stored columns for amounts paid — always computed
    // fresh from the payments table, same "single source of truth" rule
    // we used for Proposal cost totals.

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return round((float) $this->total - $this->paidAmount, 2);
    }

    /**
     * Recalculate subtotal/tax/total from current items.
     * Tax is kept simple: a flat percentage passed in, defaulting to
     * whatever is already stored on the invoice (0 if none).
     */
    public function recalculateTotals(?float $taxPercent = null): void
    {
        $subtotal = round((float) $this->items()->sum('amount'), 2);

        // If a tax percent isn't explicitly passed, back-calculate the
        // existing percent from stored tax/subtotal so edits stay consistent.
        if ($taxPercent === null) {
            $taxPercent = $this->subtotal > 0
                ? round(((float) $this->tax / (float) $this->subtotal) * 100, 2)
                : 0;
        }

        $tax = round($subtotal * ($taxPercent / 100), 2);

        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = round($subtotal + $tax, 2);
        $this->save();
    }

    /**
     * Recompute status from payments + due date.
     * Manual states (cancelled) are never overwritten automatically.
     * 'unpaid' is the implicit starting state once nothing is paid.
     */
    public function syncStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $paid = $this->paidAmount;
        $total = (float) $this->total;

        if ($total > 0 && $paid >= $total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $status = 'overdue';
        } else {
            $status = 'unpaid';
        }

        if ($this->status !== $status) {
            $this->status = $status;
            $this->save();
        }
    }
}