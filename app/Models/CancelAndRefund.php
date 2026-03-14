<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelAndRefund extends Model
{
    use HasFactory;
    protected $table = 'cancelandrefundpage';
    protected $fillable = [
        'title',
        'description'
    ];
}
