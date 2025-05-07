<?php

namespace LaraExperts\Bolt\Contracts;

use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Bolt\Fields\FieldsContract;

interface CustomSchema
{
    public function make(?FieldsContract $field = null): Accordion;

    public function hidden(?FieldsContract $field = null): array;
}
