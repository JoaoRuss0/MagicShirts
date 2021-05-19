<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ClienteUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|regex:/^[a-zA-ZÀ-ÿ ]{1,255}$/',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->id),
            ],
            'password' => 'nullable|confirmed',
            'photo' => 'nullable|image',
            'nif' => 'nullable|digits:9',
            'endereco' => 'nullable',
            'tipo_pagamento' => 'nullable|in:VISA,MC,PAYPAL',
            'ref_pagamento'=> 'nullable|required_if:tipo_pagamento,VISA,MC'
        ];
    }
}