<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class MetaToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'access_token',
        'meta_user_id',
        'page_id',
        'page_name',
        'instagram_account_id',
        'instagram_username',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function setAccessTokenAttribute(string $value): void
    {
        $this->attributes['access_token'] = encrypt($value);
    }

    public function getAccessTokenAttribute(string $value): string
    {
        return decrypt($value);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasInstagram(): bool
    {
        return ! empty($this->instagram_account_id);
    }

    public function hasFacebook(): bool
    {
        return ! empty($this->page_id);
    }
}
