<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $header = $this->faker->sentence(2);

        return [
            'header' => rtrim($header, '.'),
            'text_note' => $this->faker->text(),
            'user_id' => User::factory()
        ];
    }
}
