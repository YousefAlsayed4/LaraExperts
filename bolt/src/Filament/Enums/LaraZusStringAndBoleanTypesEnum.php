<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum LaraZusStringAndBoleanTypesEnum :string
{
    use enumHelpers;

    case DateTimePicker="\LaraZeus\Bolt\Fields\Classes\DateTimePicker";

    case TextInput="\LaraZeus\Bolt\Fields\Classes\TextInput";

    case Paragraph="\LaraZeus\Bolt\Fields\Classes\Paragraph";

    case RichEditor="\LaraZeus\Bolt\Fields\Classes\RichEditor";

    case TextArea="\LaraZeus\Bolt\Fields\Classes\Textarea";

    case Toggle="\LaraZeus\Bolt\Fields\Classes\Toggle";

}
