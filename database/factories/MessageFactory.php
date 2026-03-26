<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Post;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::inRandomOrder()->value('id'),
            'sender_id'       => User::inRandomOrder()->value('id'),
            'content'         => fake()->sentence(),
        ];
    }

}
