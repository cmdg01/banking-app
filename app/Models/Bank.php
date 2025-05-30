<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'plaid_item_id',
        'plaid_account_id',
        'plaid_access_token',
        'institution_name',
        'account_name',
        'account_type',
        'account_mask',
        'dwolla_funding_source_url',
        'plaid_cursor',
        'last_synced_at',
        'balance_available',
        'balance_current',
        'balance_limit',
        'balance_currency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'plaid_access_token',
        'plaid_cursor',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_synced_at' => 'datetime',
        'balance_available' => 'decimal:2',
        'balance_current' => 'decimal:2',
        'balance_limit' => 'decimal:2',
    ];

    /**
     * Get the user that owns the bank account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the transactions for this bank account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    
    /**
     * Get the last four digits of the account.
     */
    public function getLastFourAttribute(): string
    {
        return $this->account_mask;
    }
    
    /**
     * Check if this bank has been linked to Dwolla.
     */
    public function isDwollaLinked(): bool
    {
        return !empty($this->dwolla_funding_source_url);
    }

    /**
     * Get the status text for the bank account.
     */
    public function getStatusText(): string
    {
        return $this->status === 'active' ? 'Active' : 'Inactive'; // Adjust the logic as needed
    }
}
