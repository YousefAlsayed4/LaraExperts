<?php

namespace LaraExperts\Bolt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaraExperts\Bolt\Models\Collection;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'user_id' => config('auth.providers.users.model')::factory(),
            'values' => 'abc',
        ];
    }
}
