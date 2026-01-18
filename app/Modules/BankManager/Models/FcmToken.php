<?php

namespace App\Modules\BankManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class FcmToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Registrar ou atualizar token
    public static function registerToken(int $userId, string $token, ?string $deviceName = null): self
    {
        // Se o deviceName for fornecido, procurar token existente desse dispositivo
        if ($deviceName) {
            $existingToken = static::where('user_id', $userId)
                ->where('device_name', $deviceName)
                ->first();
            
            // Se já existe token deste dispositivo, atualizar
            if ($existingToken) {
                $existingToken->update([
                    'token' => $token,
                    'last_used_at' => now(),
                ]);
                return $existingToken;
            }
        }
        
        // Caso contrário, criar ou atualizar pelo token
        return static::updateOrCreate(
            ['user_id' => $userId, 'token' => $token],
            [
                'device_name' => $deviceName,
                'last_used_at' => now(),
            ]
        );
    }

    // Remover token
    public static function removeToken(int $userId, string $token): bool
    {
        return static::where('user_id', $userId)
            ->where('token', $token)
            ->delete() > 0;
    }

    // Obter todos os tokens de um usuário
    public static function getUserTokens(int $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('token')
            ->toArray();
    }
}
