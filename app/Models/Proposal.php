<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'brief',
        'scope',
        'timeline',
        'cost_breakdown',
        'status',
        'generation_step',
        'total_amount',
    ];

    protected $casts = [
        'scope' => 'array',
        'timeline' => 'array',
        'cost_breakdown' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}