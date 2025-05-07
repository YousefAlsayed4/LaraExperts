<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum FormFileTypes:string
{
    use EnumHelpers;
    case IMAGE= "image";

    case FILE="file";

    case VOICE_RECORD= "voice_record";

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => self::getLabel($case->value)])
            ->toArray();
    }
    
    public static function getLabel(string $value): string
    {
        return trans("enums.form_file_types.$value");
    }
}
