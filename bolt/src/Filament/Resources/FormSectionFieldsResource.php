<?php

namespace  LaraExperts\Bolt\Filament\Resources;

use AllowDynamicProperties;
use LaraExperts\Bolt\Models\Collection;
use Filament\Forms\Components\TextInput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use  LaraExperts\Bolt\Filament\Enums\LaraZusDataSourceTypesEnum;

class FormSectionFieldsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public array $fieldDataTypes;
    public function __construct($resource)
    {
        $this->fieldDataTypes=[
            'string' => __('text'),
            'email' => __('email'),
            'numeric' => __('numeric'),
            'password' => __('password'),
            'tel' => __('tel'),
            'url' => __('url'),
            'activeUrl' => __('active url'),
            'alpha' => __('alpha'),
            'alphaDash' => __('alpha dash'),
            'alphaNum' => __('alpha num'),
            'ip' => __('ip'),
            'ipv4' =>__('ip v4'),
            'ipv6' => __('ip v6'),
            'macAddress' =>__('mac address'),
        ];
        parent:: __construct($resource);
    }

    public function toArray(Request $request): array
    {
        $arr = [
            'id'=>$this->id,
            'section_id'=>$this->section_id,
            'name'=>$this->name,
            'description'=>$this->description,
            'type'=>$this->edit_field_type_response($this->type),
            'ordering'=>$this->ordering,
            'options'=>$this->edit_field_options($this->options),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'field_type'=>$this->edit_field_type($this->options)
        ];


        if ($this->type == LaraZusDataSourceTypesEnum::fileUpload->value) {
            $arr['upload_file_type'] = $arr['options']['UploadfileType'] ?? '';
        }

        return $arr;
    }

    public function edit_field_options($options)
    {
        if(isset($options['dataSource'])){
            $collection=Collection::findOrFail($options['dataSource']);
            $options['dataSource']=$collection;
        }

        return $options;
    }
    public function edit_field_type($options)
    {
        return   $this->handle_field_type_option($options);


    }

    public function handle_field_type_option($options)
    {

        if(isset($options['dateType'])) {

            if (in_array($options['dateType'], ['string', 'alpha', 'alphaDash', 'alphaNum', 'ip', 'ipv4', 'ipv6', 'macAddress'])) {

                $this->field_type = __('text');

            } else if ($options['dateType'] == 'activeUrl') {

                $this->field_type = __('url');

            } else {
                $this->field_type = $options['dateType'];
            }

        }else{
            $this->field_type =Str::lower($this->edit_field_type_response($this->type));
        }

        return  $this->field_type;
    }
    public function edit_field_type_response($type)
    {

        $lastPart = basename(str_replace('\\', '/', $type));

        return $lastPart;
    }
}
