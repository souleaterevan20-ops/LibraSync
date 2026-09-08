<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Default Super Admin
        User::create([
            'name' => 'Library Admin',
            'email' => 'admin@librasync.com',
            'password' => Hash::make('admin123'), // Securely hash the password
            'role' => 'super_admin',
            'is_verified' => true, // Bypass the gatekeeper immediately!
        ]);

        // 2. Create some initial test books
        Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '9780743273565',
            'genre' => 'Fiction',
            'type' => 'standard',
            'total_copies' => 5,
            'available_copies' => 5,
        ]);

        Book::create([
            'title' => 'Introduction to PHP & Laravel',
            'author' => 'LibraSync Team',
            'isbn' => '9781119623251',
            'genre' => 'Educational',
            'type' => 'reference', // Reference only!
            'total_copies' => 2,
            'available_copies' => 2,
        ]);
    }
}