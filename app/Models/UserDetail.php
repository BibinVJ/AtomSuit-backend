<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    const PROFILE_IMAGE_PATH = 'users/profile-images';

    protected $fillable = [
        'user_id',
        'alternate_email',
        'alternate_phone',
        'id_proof_type',
        'id_proof_number',
        'dob',
        'gender',
        'profile_image',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
