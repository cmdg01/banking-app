<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'bank_id',
        'plaid_transaction_id',
        'plaid_account_id',
        'name',
        'amount',
        'date',
        'category',
        'type',
        'payment_meta',
        'channel',
        'is_reviewed',
        'is_legitimate',
        'review_feedback',
        'reviewed_at',
        'anomaly_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'payment_meta' => 'array',
        'amount' => 'decimal:2',
        'is_reviewed' => 'boolean',
        'is_legitimate' => 'boolean',
        'reviewed_at' => 'datetime',
        'anomaly_data' => 'array',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank that owns the transaction.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
