<?php

namespace Database\Seeders;

use App\Enums\CourierLevel;
use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Budiono Hadi Agung', 'Siti Rahmawati', 'Andi Pratama', 'Dewi Lestari', 'Rizky Saputra'];

        foreach (CourierLevel::cases() as $index => $level) {
            Courier::withTrashed()->firstOrCreate(
                ['phone' => '08123456700'.$level->value],
                ['name' => $names[$index], 'email' => 'courier'.$level->value.'@example.com', 'level' => $level],
            );
        }
    }
}
