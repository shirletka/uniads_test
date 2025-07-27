<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Upload;
use Carbon\Carbon;

class CleanupFiles extends Command
{
    protected $signature = 'cleanup:files';
    protected $description = 'Удаляет файлы с истекшим сроком жизни (по формуле)';
    private const UPLOADS_FOLDER = 'uploads';

    public function handle(): int
    {
        $allUploads = Upload::all();
        $expiredUploads = $allUploads->filter(function ($upload) {
            return $upload->isExpired();
        });
        
        $deletedCount = 0;
        
        foreach ($expiredUploads as $upload) {
            $filePath = self::UPLOADS_FOLDER . '/' . $upload->path;
            
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
            
            $retentionDays = $upload->calculateRetentionPeriod();
            $sizeInMiB = number_format($upload->size / 1024 / 1024, 2);
            
            $this->line("Удален: {$upload->original_name} ({$sizeInMiB} МиБ, TTL: {$retentionDays} дней)");
            
            $upload->delete();
            $deletedCount++;
        }
        
        if ($deletedCount === 0) {
            $this->info('Нет файлов для удаления');
        } else {
            $this->info("Удалено файлов: {$deletedCount}");
        }
        
        // Показываем статистику активных файлов
        $activeCount = $allUploads->count() - $deletedCount;
        if ($activeCount > 0) {
            $this->info("Активных файлов: {$activeCount}");
        }
        
        return 0;
    }
}
