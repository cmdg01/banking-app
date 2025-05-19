<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_bank_id',
        'receiver_bank_id',
        'amount',
        'status',
        'dwolla_transfer_id',
        'dwolla_transfer_url',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'sender_bank_id');
    }

    public function receiverBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'receiver_bank_id');
    }
}