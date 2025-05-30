<?php

namespace LaraExperts\Bolt\Concerns;

use Carbon\Carbon;

trait HasUpdates
{
    public function getLastUpdatedAttribute(): string
    {
        return '<span class="text-xs text-gray-600">' . Carbon::parse($this->updated_at)->format(\Filament\Infolists\Infolist::$defaultDateTimeDisplayFormat) . '</span>';
    }
}
