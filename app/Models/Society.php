<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Society extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table='society';
    protected $fillable = [
        'society_name',
        'city_id'
    ];
}
