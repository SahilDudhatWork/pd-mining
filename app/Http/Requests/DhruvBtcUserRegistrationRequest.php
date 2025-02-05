<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class DhruvBtcUserRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:dhruv_btc_users|max:255',
            'password' => 'required|string|min:6',
            'device_id' => 'required',
            'os_version' => 'required',
            'image' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = new JsonResponse([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
