<?php

namespace LaraExperts\Bolt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Models\FieldResponse;

class FieldResponseFactory extends Factory
{
    protected $model = FieldResponse::class;

    public function definition(): array
    {
        return [
            'form_id' => BoltPlugin::getModel('Forms')::factory(),
            'field_id' => BoltPlugin::getModel('Field')::factory(),
            'response_id' => BoltPlugin::getModel('Response')::factory(),
            'response' => $this->faker->words(3, true),
        ];
    }
}
