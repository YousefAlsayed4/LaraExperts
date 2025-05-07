<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum ImageMimTypeEnum:string
{
    use EnumHelpers;
    case jepg= "jpeg";

    case jpg ="jpg";

    case png="png";

    case svg="svg";

    case webp="webp";

    case gif="gif";

}
