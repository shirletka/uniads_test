<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Upload;
use Carbon\Carbon;

class UploadController extends Controller
{
    private const MAX_KILOBYTES = 51200;
    private const UPLOADS_FOLDER = 'uploads';

    public function index()
    {
        $content = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добро пожаловать</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; line-height: 1.5; }
        pre { background: #f7f7f7; padding: 1rem; overflow-x: auto; }
        code { font-family: monospace; }
        h1 { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Минималистичный файло‑хостинг</h1>
    <p>Этот сервис позволяет загружать файлы через <code>curl</code> и получать ссылки на скачивание и удаление.</p>
    <h2>Загрузка</h2>
    <pre><code>curl -F"file=@path/to/file.ext" https://{HOST} -v</code></pre>
    <p>В ответе вы получите URL файла и заголовки:</p>
    <ul>
        <li><code>X-Delete</code> - ссылка для удаления файла</li>
        <li><code>X-Retention-Days</code> - время жизни файла в днях</li>
        <li><code>X-Expires-At</code> - дата истечения срока</li>
    </ul>
    <h2>Скачивание</h2>
    <pre><code>curl https://{HOST}/file/&lt;token&gt; -O</code></pre>
    <h2>Удаление</h2>
    <pre><code>curl https://{HOST}/delete/&lt;deleteToken&gt;</code></pre>
    <h2>Время жизни файлов</h2>
    <p>Время хранения рассчитывается динамически по размеру файла:</p>
    <ul>
        <li><strong>Малые файлы:</strong> до 1 года хранения</li>
        <li><strong>Большие файлы (>512 МиБ):</strong> минимум 30 дней</li>
        <li><strong>Формула:</strong> 30 + (30 - 365) × ((размер/512МиБ - 1)³)</li>
    </ul>
    <p>Чем больше файл, тем меньше время хранения.</p>
    <hr style="margin-top: 2rem;">
    <footer style="text-align: center; color: #666; font-size: 0.9rem;">
        <p>Задание Аширбеков Багдаулет для UniAds</p>
    </footer>
</body>
</html>
HTML;
        return response($content, 200)->header('Content-Type', 'text/html');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:' . static::MAX_KILOBYTES,
        ]);

        $file = $request->file('file');

        do {
            $token = Str::random(7);
        } while (Upload::where('token', $token)->exists());

        $deleteToken = Str::random(32);
        $path = $token;
        Storage::disk('local')->putFileAs(self::UPLOADS_FOLDER, $file, $path);

        $upload = Upload::create([
            'token'        => $token,
            'delete_token' => $deleteToken,
            'path'         => $path,
            'original_name'=> $file->getClientOriginalName(),
            'mime'         => $file->getClientMimeType(),
            'size'         => $file->getSize(),
            'created_at'   => Carbon::now(),
        ]);

        $fileUrl   = url('/file/' . $token);
        $deleteUrl = url('/delete/' . $deleteToken);
        $retentionDays = $upload->calculateRetentionPeriod();
        $expirationDate = $upload->getExpirationDate()->format('Y-m-d H:i:s');

        return response($fileUrl . "\n", 200)
            ->header('X-Delete', $deleteUrl)
            ->header('X-Retention-Days', $retentionDays)
            ->header('X-Expires-At', $expirationDate)
            ->header('Content-Type', 'text/plain');
    }

    public function show($token)
    {
        $upload = Upload::where('token', $token)->firstOrFail();
        $filePath = Storage::disk('local')->path(self::UPLOADS_FOLDER . '/' . $upload->path);
        $headers = [
            'Content-Type'        => $upload->mime,
            'Content-Disposition' => 'attachment; filename="' . $upload->original_name . '"',
        ];
        return response()->file($filePath, $headers);
    }

    public function destroy($deleteToken)
    {
        $upload = Upload::where('delete_token', $deleteToken)->firstOrFail();
        Storage::disk('local')->delete(self::UPLOADS_FOLDER . '/' . $upload->path);
        $upload->delete();
        return response("Deleted\n", 200)->header('Content-Type', 'text/plain');
    }
}
