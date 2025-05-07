<?php

namespace LaraExperts\Bolt\Filament\Enums;

enum Http: int
{
    case OK = 200;
    case BAD_REQUEST = 400;
    case Server_ERROR= 500;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
}
