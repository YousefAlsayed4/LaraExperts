<?php

namespace LaraExperts\Bolt\DataSources;

// use App\Helpers\Traits\EnumHelpers;

enum FormFileTypes:string
{
    // use EnumHelpers;
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
        return trans("$value");
    }
}
