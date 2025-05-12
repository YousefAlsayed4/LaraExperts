<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeFormControllerCommand extends Command
{
    protected $signature = 'make:form-controller';
    protected $description = 'Create FormController with API methods';

    public function handle()
    {
        $controllerPath = app_path('Http/Controllers/Api/Form/FormController.php');
        $directory = dirname($controllerPath);

        // Create directories if they don't exist
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info('Created directory: '.str_replace(base_path(), '', $directory));
        }

        // Check if controller already exists
        if (File::exists($controllerPath)) {
            $this->error('FormController already exists!');
            return;
        }

        // Generate controller content
        $content = <<<'PHP'
<?php

namespace App\Http\Controllers\Api\Form;

use LaraExperts\Bolt\Filament\Enums\FileMimTypeEnum;
use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Enums\ImageMimTypeEnum;
use LaraExperts\Bolt\Filament\Enums\LaraZusDataSourceTypesEnum;
use LaraExperts\Bolt\Filament\Enums\LaraZusStringAndBoleanTypesEnum;
use LaraExperts\Bolt\Filament\Enums\VoiceRecordMimTypeEnum;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use LaraExperts\Bolt\Filament\Helpers\Traits\StatusCheckTrait;
use App\Http\Controllers\Controller;
use LaraExperts\Bolt\Filament\Requests\Api\SubmitFormRequest;
use LaraExperts\Bolt\Filament\Resources\Api\FormResource;
use LaraExperts\Bolt\Filament\Resources\Api\FormSubmissionResource;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\Form;
use Illuminate\Support\Arr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
// use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FormController extends Controller
{
    use StatusCheckTrait;

    public function get_all_active_forms(): APIResponse
    {
        $forms = Form::where('is_active', true)
            ->with('sections.fields')
            ->latest()
            ->get();
    
        if ($forms->isEmpty()) {
            return new APIResponse(
                code: Http::OK,
                message: 'validation.forms_not_found',
                body: ["forms" => [__('validation.forms_not_found')]]
            );
        }
    
        return new APIResponse(
            code: Http::OK,
            body: ['forms' => FormResource::collection($forms)]
        );
    }

    public function submit_form(SubmitFormRequest $request)
    {
        // 1. Get the form
        $form = Form::find($request->input('form_id'));
        if (!$form) {
            return new APIResponse(
                code: Http::BAD_REQUEST,
                message: 'validation.form_not_found',
                body: ['error' => __('validation.form_not_found')]
            );
        }
    
        // 2. Process field responses
        $items = $request->input('items');
        $response = null;
    
        foreach ($items as &$item) {
            $field = Field::find($item['field_id']);
            if (!$field) continue;
    
            if (in_array($field->type, LaraZusDataSourceTypesEnum::toArray())) {
                $response = $this->handle_saving_fields($field, $item['response']);
            } elseif (in_array($field->type, LaraZusStringAndBoleanTypesEnum::toArray())) {
                $response = $this->handle_saving_string_boleans_fields($field, $item['response']);
            }
    
            if ($response instanceof APIResponse) {
                return $response;
            }
    
            // Add both form_id and response_id to each item
            $item['form_id'] = $form->id;
        }
    
        // 3. Create the main response record
        $response_record = $form->responses()->create([
            // Any additional fields here
        ]);
    
        // 4. Now add response_id to each item before creating field responses
        foreach ($items as &$item) {
            $item['response_id'] = $response_record->id;
        }
    
        // 5. Save field responses (now includes both form_id and response_id)
        $field_response = $response_record->fieldsResponses()->createMany($items);
    
        return new APIResponse(
            status: "success",
            code: Http::OK,
            message: 'validation.form_submitted_successfully',
            body: ['form_response' => FormSubmissionResource::collection($field_response)],
        );
    }


     private function handle_saving_string_boleans_fields($field, &$value)
     {


         $enum_values = LaraZusStringAndBoleanTypesEnum::toValues();

         if (in_array($field->type, array_diff($enum_values, [LaraZusStringAndBoleanTypesEnum::Toggle->value]))) {
             $response = $this->handle_inputs_validation_except_toggle($field, $value);
             return $response;

         }
         if ($field->type == LaraZusStringAndBoleanTypesEnum::Toggle->value) {
             if ($value !== true && $value !== false) {

                 return new APIResponse(
                     status: "fail",
                     code: Http::BAD_REQUEST,
                     message: __('validation.toggle_invalid', ['attribute' => $field->name]),
                     errors: [
                         'items' => [__('validation.toggle_should_be_boolean', ['attribute' => $field->name])]
                     ]
                 );
             }
             return json_encode($value);


         }

     }

     private function handle_inputs_validation_except_toggle($field, &$value)
     {

         if (!is_string($value)) {
             return new APIResponse(
                 status: "fail",
                 code: Http::BAD_REQUEST,
                 message: __('validation.field_type_should_be_string', ['attribute' => $field->name]),
                 errors: ['items' => [__('validation.field_type_should_be_string', ['attribute' => $field->name])]],
             );
         }
         if ($field->type == LaraZusStringAndBoleanTypesEnum::TextInput->value) {
             if ($field->options['dateType'] == "tel") {
                 $value = preg_replace('/[^0-9]/', '', $value);
                 if (!preg_match('/^\d{10}$/', $value)) { // Example: 10-digit phone number
                     return new APIResponse(
                         status: "fail",
                         code: Http::BAD_REQUEST,
                         message: __('validation.field_type_should_be_phone', ['attribute' => $field->name]),
                         errors: ['items' => [__('validation.field_type_should_be_phone', ['attribute' => $field->name])]],
                     );
                 }

             }
             if ($field->options['dateType'] == "url") {
                 if (!filter_var($value, FILTER_VALIDATE_URL)) {
                     return new APIResponse(
                         status: "fail",
                         code: Http::BAD_REQUEST,
                         message: __('validation.field_type_should_be_url', ['attribute' => $field->name]),
                         errors: ['items' => [__('validation.field_type_should_be_url', ['attribute' => $field->name])]],
                     );

                 }
             }
             if ($field->options['dateType'] == "email") {
                 if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                     return new APIResponse(
                         status: "fail",
                         code: Http::BAD_REQUEST,
                         message: __('validation.field_type_should_be_email', ['attribute' => $field->name]),
                         errors: ['items' => [__('validation.field_type_should_be_email', ['attribute' => $field->name])]],
                     );
                 }
             }
             if ($field->options['dateType'] == "numeric") {
                 $min_length = $field->options['minValue'] ?: 0;
                 $max_length = $field->options['maxValue'] ?: 999999;
                 if ($value < $min_length || $value > $max_length) {
                     return new APIResponse(
                         status: "fail",
                         code: Http::BAD_REQUEST,
                         message: __('validation.field_length_between', [
                             'attribute' => $field->name,
                             'min' => $min_length,
                             'max' => $max_length,
                         ]),
                         errors: [
                             'items' => [__('validation.field_length_between', [
                                 'attribute' => $field->name,
                                 'min' => $min_length,
                                 'max' => $max_length,
                             ])],
                         ]
                     );
                 }

             }
         }

         if ($field->type == LaraZusStringAndBoleanTypesEnum::TextArea->value) {
             $min_length = $field->options['minLength'] ?: 1;
             $max_length = $field->options['maxLength'] ?: 65535;

             if (strlen($value) < $min_length || strlen($value) > $max_length) {
                 return new APIResponse(
                     status: "fail",
                     code: Http::BAD_REQUEST,
                     message: __('validation.field_length_between', [
                         'attribute' => $field->name,
                         'min' => $min_length,
                         'max' => $max_length,
                     ]),
                     errors: [
                         'items' => [__('validation.field_length_between', [
                             'attribute' => $field->name,
                             'min' => $min_length,
                             'max' => $max_length,
                         ])],
                     ]
                 );
             }
         }
         if ($field->type == LaraZusStringAndBoleanTypesEnum::DateTimePicker->value) {
             $format = 'Y-m-d H:i:s'; // Define your expected format

             // Check if $value matches the expected format
             $datetime = Carbon::createFromFormat($format, $value);

             // Check for errors in formatting
             if (!$datetime || $datetime->format($format) !== $value) {
                 // Invalid datetime format
                 return new APIResponse(
                     status: "fail",
                     code: Http::BAD_REQUEST,
                     message: __('validation.invalid_datetime', ['attribute' => $field->name]),
                     errors: [
                         'items' => [__('validation.invalid_datetime', ['attribute' => $field->name])]
                     ]
                 );
             }
         }
         return json_encode($value);
     }

    // /**
    //  * @param $field
    //  * @param $value
    //  * @return APIResponse|false|mixed|string
    //  * handle saving all fields
    //  */
     private function handle_saving_fields($field, &$value)
     {


         //fields that have dataSource the fields that allow multiple options (checkboxes-select-fileUpload)
         if (isset($field->options['dataSource'])) {

             $response = $this->validate_data_source($value, $field);

             return $response;

         }
         if ($field->type == LaraZusDataSourceTypesEnum::fileUpload->value) {

             $value = $this->handle_media_saving($field, $value);
         }


         if ($field->type == LaraZusDataSourceTypesEnum::SelectMenu->value && $field->options['allow_multiple'] == true) {
             $value = json_encode($value);

         }

         return $value;
     }
    private function validate_data_source(&$value, $field)
    {
        // get itemKey (field values that should send in request as array (for items that allow send multiple data
        $dataSource_array = array_column(\LaraExperts\Bolt\Models\Collection::find($field->options['dataSource'])->values->toArray(), 'itemKey');

        if (isset($field->options['allow_multiple']) && $field->options['allow_multiple'] == true && !is_array($value)) {

            return new APIResponse(
                status: "fail",
                code: Http::BAD_REQUEST,
                message: __('validation.field_type_should_be_array', ['attribute' => $field->name]),
                errors: ['items' => [__('validation.field_type_should_be_array', ['attribute' => $field->name])]],
            );
        }
        if ($field->type == LaraZusDataSourceTypesEnum::Radio->value && is_array($value)) {
            return new APIResponse(
                status: "fail",
                code: Http::BAD_REQUEST,
                message: __('validation.field_type_should_be_string', ['attribute' => $field->name]),
                errors: ['items' => [__('validation.field_type_should_be_string', ['attribute' => $field->name])]],
            );
        }
        if (is_array($value)) {
            // If $value is an array, check if any element is in $dataSource_array
            $result = empty(array_diff($value, $dataSource_array));
            $value = json_encode($value);

        } else {
            // If $value is a string, directly check if it's in $dataSource_array
            $result = in_array($value, $dataSource_array);

            $value = json_encode($value);


        }

        if (!$result) {

            return new APIResponse(
                status: "fail",
                code: Http::BAD_REQUEST,
                message: __('validation.invalid_data_source_value', ['attribute' => $field->name]),
                errors: ['items' => [__('validation.invalid_data_source_value', ['attribute' => $field->name])]],
            );
        }

        return $value;

    }


}




PHP;

        File::put($controllerPath, $content);
        $this->info('FormController created successfully: '.str_replace(base_path(), '', $controllerPath));
    }
}