<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'organization_name' => 'required|string|max:255',
            'organization_code' => 'required|string|max:50|unique:organizations',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'max_institutions' => 'required|integer|min:1',
        ];
    }
}
