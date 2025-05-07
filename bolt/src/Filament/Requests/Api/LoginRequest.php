<?php

namespace LaraExperts\Bolt\Filament\Requests\Api;

use LaraExperts\Bolt\Filament\Enums\Http;
use App\Rules\LoginTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;

class LoginRequest extends FormRequest
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
            'username_email' => [
                'required',
                'string',
            ],
            'password' => 'required|string|min:6',
        ];
    }
    
    public function messages(): array
    {
        return [
            'username_email.required' => __('validation.required', ['attribute' => __('auth.username_or_email_required')]),
            'username_email.string' => __('validation.string', ['attribute' => __('auth.username_or_email_should_be_string')]),
            'password.required' => __('validation.required', ['attribute' => __('auth.password_required')]),
            'password.min' => __('validation.min', ['attribute' => __('auth.password_min'), 'min' => 6]),
        ];
    }

   /**
 * Handle failed validation.
 *
 * @param Validator $validator
 * @throws HttpResponseException
 */
public function failedValidation(Validator $validator)
{
    throw new HttpResponseException(
            new APIResponse(
                status: "fail",
                code: Http::BAD_REQUEST,
                message: __('validation.validation_error'),
                errors: $validator->errors()->toArray()
            ),
        
    );
}


}
