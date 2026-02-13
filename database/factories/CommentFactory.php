<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Post;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'user_id' => User::inRandomOrder()->value('id'),
        'post_id' => Post::inRandomOrder()->value('id'),
        'content' => fake()->sentence(),
    ];
}

}
