<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@librorum.com'],
            [
                'username' => 'admin',
                'password_hash' => bcrypt('admin123'),
                'role' => 'admin',
                'join_date' => now(),
            ]
        );

        // Create test users
        $user1 = \App\Models\User::firstOrCreate(
            ['email' => 'marks@example.com'],
            [
                'username' => 'marks_bruveris',
                'password_hash' => bcrypt('password123'),
                'role' => 'admin',
                'join_date' => now(),
            ]
        );

        $user2 = \App\Models\User::firstOrCreate(
            ['email' => 'anna@example.com'],
            [
                'username' => 'lasitajs_anna',
                'password_hash' => bcrypt('password123'),
                'role' => 'user',
                'join_date' => now()->subDays(30),
            ]
        );

        $user3 = \App\Models\User::firstOrCreate(
            ['email' => 'janis@example.com'],
            [
                'username' => 'admin_janis',
                'password_hash' => bcrypt('password123'),
                'role' => 'admin',
                'join_date' => now()->subDays(60),
            ]
        );

        // Create test books
        $book1 = \App\Models\Book::create([
            'title' => 'Harry Potter un Filozofu akmens',
            'author' => 'J.K. Rowling',
            'isbn' => '9780747532699',
            'cover_image_url' => 'https://example.com/harry-potter.jpg',
            'page_count' => 223,
            'tag' => 'fantastika',
        ]);

        $book2 = \App\Models\Book::create([
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => '9780451524935',
            'cover_image_url' => 'https://example.com/1984.jpg',
            'page_count' => 328,
            'tag' => 'dystopija',
        ]);

        // Create reading progress
        \App\Models\ReadingProgress::create([
            'user_id' => $user1->user_id,
            'book_id' => $book1->book_id,
            'current_page' => 150,
            'status' => 'reading',
            'last_updated' => now(),
        ]);

        \App\Models\ReadingProgress::create([
            'user_id' => $user2->user_id,
            'book_id' => $book2->book_id,
            'current_page' => 328,
            'status' => 'completed',
            'last_updated' => now()->subDays(5),
        ]);

        // Create discussion threads
        $thread1 = \App\Models\Thread::create([
            'user_id' => $user1->user_id,
            'book_id' => $book1->book_id,
            'title' => 'Kas jums šķiet par Hermioni?',
            'content' => 'Man šķiet, ka Hermione ir ļoti gudra un drosmīga. Ko jūs domājat?',
            'scope' => 'book',
            'created_at' => now(),
        ]);

        $thread2 = \App\Models\Thread::create([
            'user_id' => $user2->user_id,
            'book_id' => $book2->book_id,
            'title' => 'Kas jums šķiet par 1984 aktualitāti?',
            'content' => 'Pēc grāmatas izlasīšanas man radās daudz domu par Orwella vīziju un tās saistību ar mūsdienu pasauli.',
            'scope' => 'book',
            'created_at' => now()->subHours(2),
        ]);

        // Create comments
        \App\Models\Comment::create([
            'thread_id' => $thread1->thread_id,
            'user_id' => $user2->user_id,
            'content' => 'Piekrītu! Hermione ir fantastiska rakstura attīstība.',
            'created_at' => now()->subMinutes(30),
        ]);

        // Create following relationship
        \DB::table('following')->insert([
            'follower_id' => $user1->user_id,
            'followee_id' => $user2->user_id,
            'created_at' => now()->subDays(10),
        ]);

        // Create likes
        \App\Models\Like::create([
            'user_id' => $user2->user_id,
            'target_type' => 'thread',
            'target_id' => $thread1->thread_id,
            'created_at' => now()->subMinutes(15),
        ]);
    }
}
