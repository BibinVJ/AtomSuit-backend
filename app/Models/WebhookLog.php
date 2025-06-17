<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'url',
        'method',
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
