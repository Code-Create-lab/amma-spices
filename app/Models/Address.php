<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

class Address extends Model
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'address';
    protected $primaryKey = 'address_id';
    public $timestamps = true;
    const CREATED_AT = 'added_at';
    protected $fillable = [
        'type',
        'user_id',
        'receiver_name',
        'receiver_phone',
        'alternate_phone',
        'city',
        'city_id',
        'society_id',
        'society',
        'house_no',
        'landmark',
        'state',
        'pincode',
        'lat',
        'lng',
        'select_status',
        'receiver_email',
        'is_deleted',
        'uuid'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
    protected $hidden = [
        // 'receiver_phone',
        // 'pincode'
    ];
    protected $attributes = [
        'landmark'  => NULL,
    ];

        
     public function user(){

        return $this->belongsTo(User::class); 
    }
}
