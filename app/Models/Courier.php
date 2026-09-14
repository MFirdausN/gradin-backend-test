<?php

namespace App\Models;

use App\Enums\CourierLevel;
use Database\Factories\CourierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    /** @use HasFactory<CourierFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'level', 'is_active'];

    protected function casts(): array
    {
        return ['level' => CourierLevel::class, 'is_active' => 'boolean'];
    }
}
