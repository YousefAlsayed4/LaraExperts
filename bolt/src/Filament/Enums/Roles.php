<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum Roles: string
{
    use EnumHelpers;
    case superAdmin="Super Admin";
    case company_admin="Company Admin";
    case subAdmin="Sub Admin";
    case AppUser="App User";
}
