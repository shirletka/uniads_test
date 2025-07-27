<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Upload extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'token',
        'delete_token',
        'path',
        'original_name',
        'mime',
        'size',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Рассчитывает время жизни файла по формуле.
     * min_age = 30 days
     * max_age = 1 year
     * max_size = 512.0 MiB
     * retention = min_age + (min_age - max_age) * pow((file_size / max_size - 1), 3)
     */
    public function calculateRetentionPeriod(): int
    {
        $minAge = 30; // дней
        $maxAge = 365; // дней (1 год)
        $maxSize = 512 * 1024 * 1024; // 512 МиБ в байтах
        
        $fileSizeRatio = $this->size / $maxSize;
        
        // Формула из задания
        $retention = $minAge + ($minAge - $maxAge) * pow(($fileSizeRatio - 1), 3);
        
        // Ограничиваем результат между минимальным и максимальным возрастом
        return (int) max($minAge, min($maxAge, $retention));
    }

    /**
     * Проверяет, истекло ли время жизни файла.
     */
    public function isExpired(): bool
    {
        $retentionDays = $this->calculateRetentionPeriod();
        $expirationDate = $this->created_at->addDays($retentionDays);
        
        return Carbon::now()->isAfter($expirationDate);
    }

    /**
     * Возвращает дату истечения срока жизни файла.
     */
    public function getExpirationDate(): Carbon
    {
        $retentionDays = $this->calculateRetentionPeriod();
        return $this->created_at->addDays($retentionDays);
    }
}
