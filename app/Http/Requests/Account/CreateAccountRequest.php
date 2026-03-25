<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:COURANT,EPARGNE,MINEUR',
            'guardian_id' => 'required_if:type,MINEUR|exists:users,id|nullable',
            'overdraft_limit' =>'numeric|min:0|nullable',
            'interest_rate' =>'numeric|min:0|max:100|nullable',
            'monthly_fee' =>'numeric|min:0|nullable',
        ];
    }
}
