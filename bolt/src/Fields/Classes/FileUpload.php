<?php

namespace LaraExperts\Bolt\Fields\Classes;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Storage;
use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Accordion\Forms\Accordions;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Fields\FieldsContract;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\FieldResponse;

class FileUpload extends FieldsContract
{
    public string $renderClass = \Filament\Forms\Components\FileUpload::class;

    public int $sort = 11;

    public function title(): string
    {
        return __('File Upload');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('single or multiple file uploader');
    }

    public static function getOptions(?array $sections = null): array
    {
        return [
            Accordions::make('check-list-options')
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            \Filament\Forms\Components\Toggle::make('options.allow_multiple')->label(__('Allow Multiple')),
                            self::required(),
                            self::columnSpanFull(),
                            self::hiddenLabel(),
                            self::htmlID(),
                            self::uploadFileType(),
                        ]),
                    self::hintOptions(),
                    self::visibility($sections),
                    Bolt::getCustomSchema('field', resolve(static::class)) ?? [],
                ]),
        ];
    }

    public static function getOptionsHidden(): array
    {
        return [
            ...Bolt::getHiddenCustomSchema('field', resolve(static::class)) ?? [],
            self::hiddenHtmlID(),
            self::hiddenHintOptions(),
            self::hiddenRequired(),
            self::hiddenColumnSpanFull(),
            self::hiddenHiddenLabel(),
            self::hiddenVisibility(),
            self::hiddenUploadFileType(),
            Hidden::make('options.allow_multiple')->default(false),
        ];
    }

    public function getResponse(Field $field, FieldResponse $resp): string
    {
        $responseValue = filled($resp->response) ? Bolt::isJson($resp->response) ? json_decode($resp->response) : [$resp->response] : [];

        $disk = Storage::disk(config('form-bolt.uploadDisk'));

        $getUrl = fn ($file) => config('form-bolt.uploadVisibility') === 'private'
            ? $disk->temporaryUrl($file, now()->addDay())
            : $disk->url($file);

        return view('form::filament.fields.file-upload')
            ->with('resp', $resp)
            ->with('responseValue', $responseValue)
            ->with('field', $field)
            ->with('getUrl', $getUrl)
            ->render();
    }

    public function TableColumn(Field $field): ?\Filament\Tables\Columns\Column
    {
        return null;
    }

    // @phpstan-ignore-next-line
    public function appendFilamentComponentsOptions($component, $zeusField, bool $hasVisibility = false)
    {
        parent::appendFilamentComponentsOptions($component, $zeusField, $hasVisibility);

        $component->disk(config('form-bolt.uploadDisk'))
            ->directory(config('form-bolt.uploadDirectory'))
            ->visibility(config('form-bolt.uploadVisibility'));

        if (isset($zeusField->options['allow_multiple']) && $zeusField->options['allow_multiple']) {
            $component = $component->multiple();
        }

        if (isset($zeusField->options['UploadfileType']) && request()->filled($zeusField->options['UploadfileType'])) {
            $component = $component->required()->default(request()->input($zeusField->options['UploadfileType']));
        }

        return $component;
    }

    public static function hiddenUploadFileType(): array
    {
        return [
            Hidden::make('options.UploadfileType'),
        ];
    }
}
