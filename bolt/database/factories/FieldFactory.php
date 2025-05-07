<?php

namespace LaraExperts\Bolt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Models\Field;

class FieldFactory extends Factory
{
    protected $model = Field::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => '\LaraExperts\Bolt\Fields\Classes\TextInput',
            'section_id' => BoltPlugin::getModel('Section')::factory(),
            'ordering' => $this->faker->numberBetween(1, 20),
        ];
    }
}
