<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnAndExchange extends Model
{
    use HasFactory;
    protected $table = 'returnandexchangepage';
    protected $fillable = [
        'title',
        'description'
    ];
}
