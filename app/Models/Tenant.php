<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_key',
        'api_secret',
        'domain',
        'email',
        'phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function metaToken(): HasOne
    {
        return $this->hasOne(MetaToken::class);
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public function isMetaConnected(): bool
    {
        return $this->metaToken()->exists();
    }

    /**
     * Gera novas credenciais para consumo da API (OpenCart).
     *
     * @return array{api_key: string, api_secret_plain: string}
     */
    public function regenerateApiCredentials(): array
    {
        $apiKey = 'ins_' . Str::lower(Str::random(24));
        $apiSecretPlain = Str::random(48);

        $this->forceFill([
            'api_key' => $apiKey,
            'api_secret' => Hash::make($apiSecretPlain),
        ])->save();

        return [
            'api_key' => $apiKey,
            'api_secret_plain' => $apiSecretPlain,
        ];
    }
}
