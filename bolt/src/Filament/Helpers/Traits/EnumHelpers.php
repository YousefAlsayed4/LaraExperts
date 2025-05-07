<?php

namespace LaraExperts\Bolt\Filament\Helpers\Traits;

trait EnumHelpers
{
    public static function toValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function toArray(): array
    {
        $translatedEnums = [];
        foreach (self::cases() as $case) {
            $translatedEnums[$case->name] = __($case->value);
        }
        return $translatedEnums;
    }

    public static function displayRoles(): array
    {
        return [
            'company_admin' => 'Company Admin',
            'subAdmin' => 'Sub Admin',
            'AppUser' => 'App User',
        ];
    }

}
