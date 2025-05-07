<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum LaraZusDataSourceTypesEnum: string
{
    use enumHelpers;

    case  SelectMenu ="\LaraZeus\Bolt\Fields\Classes\Select";

    case checkBox="\LaraZeus\Bolt\Fields\Classes\CheckboxList";

    case fileUpload="\LaraZeus\Bolt\Fields\Classes\FileUpload";

    case Radio="\LaraZeus\Bolt\Fields\Classes\Radio";

}
