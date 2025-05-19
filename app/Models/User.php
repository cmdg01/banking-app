<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'date_of_birth',
        'ssn',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ssn',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    /**
     * Determine if the user has a linked Dwolla customer account.
     *
     * @return bool
     */
    public function hasDwollaAccount(): bool
    {
        return !is_null($this->dwolla_customer_url);
    }

    /**
     * Get the user's initials.
     *
     * @return string
     */
    public function initials(): string
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';

        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }

        return $initials;
    }

    /**
     * Get the bank accounts for the user.
     */
    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }

    /**
     * Get all transfers where the user is the sender.
     */
    public function sentTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'sender_id');
    }

    /**
     * Get all transfers where the user is the receiver.
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'receiver_id');
    }

    /**
     * Get a merged collection of all transfers (sent + received).
     * This is an in-memory method, useful after loading the user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllTransfersAttribute()
    {
        return $this->sentTransfers->merge($this->receivedTransfers);
    }
}