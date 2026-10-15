<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_page',
        'exit_page',
        'page_views',
        'duration_seconds',
        'started_at',
        'last_activity_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class, 'visitor_session_id', 'id');
    }
}
