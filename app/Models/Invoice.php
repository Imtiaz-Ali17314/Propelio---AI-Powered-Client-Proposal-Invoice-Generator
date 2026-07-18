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

    /**
     * Recalculate subtotal & total based on current items.
     * Call this after items are added/updated/removed.
     */
    public function recalculateTotals(float $taxRate = 0): void
    {
        $subtotal = $this->items()->sum('amount');
        $tax = $subtotal * ($taxRate / 100);

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
}