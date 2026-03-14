<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';


     public function address(){

        return $this->hasMany(Address::class, 'user_id', 'id'); 
    }
}