<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'repo', 'description', 'stack', 'demo_url'];

    protected function casts(): array
    {
        return [
            'stack' => 'array',
        ];
    }
}
