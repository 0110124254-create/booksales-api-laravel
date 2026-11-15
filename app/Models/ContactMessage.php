<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'name', 'email', 'subject', 'message', 'read_at'])]
class ContactMessage extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
