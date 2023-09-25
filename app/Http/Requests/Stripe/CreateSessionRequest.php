<?php

namespace App\Http\Requests\Stripe;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class CreateSessionRequest extends FormRequest
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
            'secret_key' => 'required',
            'currency' => 'required',
            'unit_amount' => 'required',
            'quantity' => 'required',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->first();

        throw new HttpResponseException(
            responseJson(false, $errors , [] , JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
