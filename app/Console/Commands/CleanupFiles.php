<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Upload;
use Carbon\Carbon;

class CleanupFiles extends Command
{
    protected $signature = 'cleanup:files';
    protected $description = 'Удаляет файлы старше заданного TTL';
    private const TTL_DAYS = 7;
    private const UPLOADS_FOLDER = 'uploads';

    public function handle(): int
    {
        $expiration = Carbon::now()->subDays(self::TTL_DAYS);
        $expired = Upload::where('created_at', '<', $expiration)->get();
        $count = 0;
        foreach ($expired as $upload) {
            Storage::disk('local')->delete(self::UPLOADS_FOLDER . '/' . $upload->path);
            $upload->delete();
            $count++;
        }
        $this->info('Удалено файлов: ' . $count);
        return 0;
    }
}
