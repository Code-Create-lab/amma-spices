<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermAndCondition extends Model
{
    use HasFactory;
    public $primaryKey = 'term_id';
    protected $table='termspage';
    protected $fillable = [
        'title',
        'description'
    ];
}
