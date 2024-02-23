<?php

namespace App\Http\Requests\Paypal;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class RetrieveOrderRequest extends FormRequest
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
            'client_id' => 'required',
            'client_secret' => 'required',
            'token' => 'required|in:' . env("API_TOKEN",""),
            'order_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'token.in' => 'The token is incorrect'
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), [
            'token' => $this->header('token'),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->first();

        throw new HttpResponseException(
            responseJson(false, $errors, [], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
