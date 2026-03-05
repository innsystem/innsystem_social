<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'external_product_id',
        'source_domain',
        'platform',
        'meta_post_id',
        'caption',
        'image_url',
        'status',
        'error_message',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Contagem de posts publicados hoje (controle de rate limit da Meta: 50/dia).
     */
    public static function dailyCountForTenant(int $tenantId, string $platform): int
    {
        return static::where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->whereDate('created_at', today())
            ->where('status', 'published')
            ->count();
    }
}
