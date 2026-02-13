<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Profile;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
public function run(): void
{
    User::factory(20)->create()->each(function ($user) {

        Profile::factory()->create([
            'user_id' => $user->id
        ]);

    });

    Post::factory(50)->create();
    Comment::factory(200)->create();
}
}
