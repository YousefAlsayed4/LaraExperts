<?php

namespace LaraExperts\Bolt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Models\Section;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'form_id' => BoltPlugin::getModel('Form')::factory(),
            'ordering' => $this->faker->numberBetween(1, 10),
        ];
    }
}
