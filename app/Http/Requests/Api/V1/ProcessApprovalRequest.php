<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProcessApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:approved,rejected'],
            'rejection_note' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
        ];
    }
}
