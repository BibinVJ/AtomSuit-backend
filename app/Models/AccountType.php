<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    use AppAudit;

    protected $fillable = [
        'name',
        'code',
        'class',
    ];

    public function accountGroups(): HasMany
    {
        return $this->hasMany(AccountGroup::class);
    }
}
