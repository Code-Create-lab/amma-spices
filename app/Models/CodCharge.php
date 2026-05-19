<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodCharge extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cod_charges';
    protected $fillable = ['title', 'cod_charge', 'status'];
}
