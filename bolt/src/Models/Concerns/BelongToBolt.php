<?php

namespace LaraExperts\Bolt\Models\Concerns;

trait BelongToBolt
{
    public static function getBoltUserFullNameAttribute(): string
    {
        return 'name';
    }
}
