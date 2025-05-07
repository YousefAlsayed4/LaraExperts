<?php

namespace LaraExperts\Bolt\Contracts;

use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\FieldResponse;

interface Fields
{
    public function title(): string;

    public function getResponse(Field $field, FieldResponse $resp): string;
}
