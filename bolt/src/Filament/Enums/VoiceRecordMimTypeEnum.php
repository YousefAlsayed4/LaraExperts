<?php

namespace LaraExperts\Bolt\Filament\Enums;

use LaraExperts\Bolt\Filament\Helpers\Traits\EnumHelpers;

enum VoiceRecordMimTypeEnum:string
{
    use EnumHelpers;
    case mp4 = 'mp4';
    case webm = 'webm';
    case wmv = 'wmv';
    case asf = 'asf';
    case wav = 'wav';
    case avi = 'avi';
    case mp3 = 'mp3';
    case m4a = 'm4a';
}
