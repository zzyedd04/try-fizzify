<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['type', 'amount', 'status', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'integer'];
    }
}
