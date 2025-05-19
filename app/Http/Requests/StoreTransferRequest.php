<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_bank_id' => [
                'required',
                Rule::exists('banks', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id())
                        ->whereNotNull('dwolla_funding_source_url');
                }),
            ],
            'destination_type' => ['required', 'in:own_account,other_user'],
            'destination_bank_id' => [
                'required_if:destination_type,own_account',
                Rule::exists('banks', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id())
                        ->whereNotNull('dwolla_funding_source_url');
                }),
            ],
            'recipient_email' => ['required_if:destination_type,other_user', 'email'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000.00'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source_bank_id.required' => 'Please select a source account.',
            'source_bank_id.exists' => 'The selected source account is invalid or not linked to Dwolla.',
            'destination_type.required' => 'Please specify the destination type.',
            'destination_bank_id.required_if' => 'Please select a destination account.',
            'destination_bank_id.exists' => 'The selected destination account is invalid or not linked to Dwolla.',
            'recipient_email.required_if' => 'Please enter the recipient\'s email address.',
            'recipient_email.email' => 'Please enter a valid email address.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The minimum transfer amount is $0.01.',
            'amount.max' => 'The maximum transfer amount is $10,000.00.',
        ];
    }
}
