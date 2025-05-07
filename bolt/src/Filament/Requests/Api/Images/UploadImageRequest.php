<?php

namespace LaraExperts\Bolt\Filament\Requests\Api\Images;

use LaraExperts\Bolt\Filament\Enums\FormFileTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
      return  true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules=[
            'files' => ['required', 'array'], // Ensure 'files' is an array
            'files.*' => [ 'max:30720'], // Validate each file in the array (30 MB limit per file)
            "file_type"=>['required',"string",Rule::in(FormFileTypes::toValues())]
        ];
        $fileType = $this->request->get('file_type');
     switch ($fileType){
         case FormFileTypes::IMAGE->value:
             array_push($rules['files.*'],'mimes:jpeg,jpg,png,svg,webp,gif');
             break;
         case FormFileTypes::FILE->value:
             array_push($rules['files.*'],'mimes:odt,ppt,pptx,doc,docx,pdf,csv,txt,json,html,xls,xlsx');

             break;
         case FormFileTypes::VOICE_RECORD->value:
             array_push($rules['files.*'],'mimes:mp4,webm,wmv,asf,wav,avi,mp3,m4a');
             break;
     }





        return  $rules;


    }




}
