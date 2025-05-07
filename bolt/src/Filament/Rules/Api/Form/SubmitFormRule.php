<?php

namespace LaraExperts\Bolt\Filament\Rules\Api\Form;

use App\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use LaraExperts\Bolt\Filament\Helpers\Traits\StatusCheckTrait;
use LaraExperts\Bolt\Models\Form;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;use Illuminate\Validation\ValidationException;

class SubmitFormRule implements ValidationRule
{
    use StatusCheckTrait;
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response= $this->check_user_company_activity(auth()->user(),auth()->user()->company);

        if ($response instanceof APIResponse) {
            // Throw an HTTP exception with the response details
            throw new HttpResponseException($response);
        }
        // get form and check if it is active and related to auth user
        $form = Form::query()->where('id', request()->form_id)
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->first();

        if (empty($form)) {
            $this->throwValidationException();
        }

    }
    /**
     * Throw a custom validation exception.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwValidationException(): void
    {
        throw ValidationException::withMessages(["form_id"=>[__('validation.form_not_found')]]);
    }


}
