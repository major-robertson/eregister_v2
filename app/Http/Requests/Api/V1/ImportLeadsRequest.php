<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportLeadsRequest extends FormRequest
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
            'leads' => ['required', 'array', 'min:1', 'max:100'],
            'leads.*.record_id' => ['required', 'string', 'max:255'],
            'leads.*.record_date' => ['nullable', 'date'],
            'leads.*.business_name' => ['nullable', 'string', 'max:255'],
            'leads.*.contact_name' => ['nullable', 'string', 'max:255'],
            'leads.*.email' => ['nullable', 'email', 'max:255'],
            'leads.*.phone' => ['nullable', 'string', 'max:20'],
            'leads.*.mailing_address' => ['nullable', 'string', 'max:255'],
            'leads.*.mailing_address_2' => ['nullable', 'string', 'max:255'],
            'leads.*.mailing_city' => ['nullable', 'string', 'max:100'],
            'leads.*.mailing_state' => ['nullable', 'string', 'max:2'],
            'leads.*.mailing_zip' => ['nullable', 'string', 'max:10'],
            'leads.*.property_address' => ['nullable', 'string', 'max:255'],
            'leads.*.property_address_2' => ['nullable', 'string', 'max:255'],
            'leads.*.property_city' => ['nullable', 'string', 'max:100'],
            'leads.*.property_state' => ['nullable', 'string', 'max:2'],
            'leads.*.property_zip' => ['nullable', 'string', 'max:10'],
            'leads.*.permit_or_external_id' => ['nullable', 'string', 'max:255'],
            'leads.*.raw_category' => ['nullable', 'string', 'max:255'],
            'leads.*.title' => ['nullable', 'string', 'max:500'],
            'leads.*.description' => ['nullable', 'string'],
            'leads.*.estimated_cost' => ['nullable', 'numeric'],
            'leads.*.source_url' => ['nullable', 'url', 'max:2048'],
            'leads.*.role' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * Return JSON response for API requests.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
