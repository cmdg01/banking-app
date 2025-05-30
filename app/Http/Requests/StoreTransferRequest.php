<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'source_bank_id' => 'required|exists:banks,id',
            'destination_bank_id' => 'required|exists:banks,id|different:source_bank_id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'source_bank_id.required' => 'Please select a source account.',
            'destination_bank_id.required' => 'Please select a destination account.',
            'destination_bank_id.different' => 'Source and destination accounts must be different.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least $0.01',
        ];
    }
}