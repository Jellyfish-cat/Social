<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Profile;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Users
  $users = User::all();

if ($users->count() < 5) {
    $users = User::factory(5 - $users->count())->create();
}

// Profiles (chỉ tạo nếu chưa có)
$users->each(function ($user) {
    if (!$user->profile) {
        Profile::factory()->create([
            'user_id' => $user->id
        ]);
    }
});

// Conversations
$conversations = Conversation::factory(5)->create();

// Pivot
foreach ($conversations as $conversation) {
    $randomUsers = $users->random(rand(2, 4));

    foreach ($randomUsers as $user) {
        DB::table('conversation_user')->insertOrIgnore([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
        ]);
    }
}

// Messages
foreach ($conversations as $conversation) {

    $userIds = DB::table('conversation_user')
        ->where('conversation_id', $conversation->id)
        ->pluck('user_id');

    for ($i = 0; $i < 20; $i++) {
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userIds->random(),
            'content' => fake()->sentence(),
            'created_at' => now(),
        ]);
    }
}
}
}