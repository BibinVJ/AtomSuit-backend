<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'url',
        'method',
        'event_type',
        'event_id',
        'headers',
        'payload',
        'response_status',
        'response_body',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'response_body' => 'array',
    ];
}
