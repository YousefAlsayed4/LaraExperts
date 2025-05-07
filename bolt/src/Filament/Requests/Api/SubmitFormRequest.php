<?php

namespace LaraExperts\Bolt\Filament\Requests\Api;

use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use LaraExperts\Bolt\Filament\Rules\Api\Form\RequiredFormFieldRule;
use LaraExperts\Bolt\Filament\Rules\Api\Form\SubmitFormRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SubmitFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow unauthenticated access if not needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                Rule::exists(config('form-bolt.table-prefix').'forms', 'id')
                    ->where('is_active', true) // Optional: only allow active forms
            ],
            'items' => 'required|array',
            'items.*.field_id' => [
                'required',
                'exists:'.config('form-bolt.table-prefix').'fields,id'
            ],
            'items.*.response' => ['required'],
        ];
    }

}
