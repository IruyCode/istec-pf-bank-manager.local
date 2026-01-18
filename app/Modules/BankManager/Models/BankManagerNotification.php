<?php

namespace App\Modules\BankManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BankManagerNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'context',
        'data',
        'link',
        'is_read',
        'is_dismissed',
        'triggered_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Métodos auxiliares
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function dismiss(): void
    {
        $this->update(['is_dismissed' => true]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    // Verificar se já existe notificação ativa com mesmo contexto
    public static function existsActive(string $context): bool
    {
        return static::where('context', $context)
            ->where('is_dismissed', false)
            ->exists();
    }

    // Criar notificação se não existir
    public static function createIfNotExists(array $data): ?self
    {
        if (static::existsActive($data['context'])) {
            return null;
        }

        return static::create($data);
    }
}
