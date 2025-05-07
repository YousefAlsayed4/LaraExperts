<?php

namespace LaraExperts\Bolt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Models\Response;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition(): array
    {
        return [
            'form_id' => BoltPlugin::getModel('Form')::factory(),
            'status' => 'NEW',
            'user_id' => 1,
            'notes' => $this->faker->text(),
        ];
    }
}
