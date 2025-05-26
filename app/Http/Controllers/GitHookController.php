<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
class GitHookController extends Controller
{
    public function handle(Request $request)
    {
        if ($this->isUpdateInProgress()) {
            return response()->json(['message' => 'Обновление уже запущено. Пожалуйста, повторите попытку позже.'], 423);
        }
        $this->lockUpdate();

        try {
            $secretKey = $request->input('secret_key');
            if ($secretKey !== config('app.git_secret_key')) {
                return response()->json(['message' => 'Недопустимый секретный ключ'], 403);
            }
            $ip = $request->ip();
            Log::info("Git hook triggered by IP: $ip");
            $this->switchToMainBranch();
            $this->discardChanges();
            $this->pullLatestChanges();
            return response()->json(['message' => 'Проект успешно обновлен'], 200);
        } catch (\Exception $e) {
            Log::error("Ошибка во время обновления git: " . $e->getMessage());
            return response()->json(['message' => 'Не удалось обновить проект'], 500);
        } finally {
            $this->unlockUpdate();
        }
    }
    private function isUpdateInProgress()
    {
        return file_exists(storage_path('app/update.lock'));
    }
    private function lockUpdate()
    {
        file_put_contents(storage_path('app/update.lock'), time());
    }
    private function unlockUpdate()
    {
        if (file_exists(storage_path('app/update.lock'))) {
            unlink(storage_path('app/update.lock'));
        }
    }
    private function switchToMainBranch()
    {
        exec('git checkout main 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception("Не удалось переключиться на основную ветку: " . implode("\n", $output));
        }
        Log::info("Переключился на основную ветку");
    }
    private function discardChanges()
    {
        exec('git reset --hard HEAD 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception("Не удалось отменить изменения: " . implode("\n", $output));
        }
        Log::info("Отменил все изменения");
    }
    private function pullLatestChanges()
    {
        exec('git pull origin main 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception("Не удалось извлечь последние изменения: " . implode("\n", $output));
        }
        Log::info("Извлек последние изменения из репозитория");
    }
}