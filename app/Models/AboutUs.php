<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;
    public $primaryKey = 'about_id';
    protected $table ='aboutuspage';
    protected $fillable = [
        'title',
        'description'
    ];
}
