<?php

namespace LaraExperts\Bolt\Filament\Rules\Api\Form;

use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\Form;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class RequiredFormFieldRule implements ValidationRule
{

    /**
     * Property to hold validation messages.
     */
    protected array $validationMessages = [];
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $form=Form::find(request()->form_id);

        $fieldIds = $form->fields()
            ->where('bolt_fields.options->is_required', true)
            ->pluck('bolt_fields.id')
            ->toArray();


        $requested_field_ids=array_column($value,'field_id');

        $not_provided_required_fields=array_diff($fieldIds,$requested_field_ids);
        $fields_not_sent_in_request=Field::query()->whereIn('id',$not_provided_required_fields)->pluck('name')->toArray();

        $this->validationMessages = collect($fields_not_sent_in_request)->mapWithKeys(function ($field) {
            return [$field => ["{$field} field is required."]];
        })->toArray();

        if (!empty($not_provided_required_fields)) {
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
        throw ValidationException::withMessages($this->validationMessages);
    }



}
