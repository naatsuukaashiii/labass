<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\LogRequest;
class LogRequestsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('LogRequestsMiddleware: Request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'request_body' => $this->maskSensitiveData($request->all()),
            'request_headers' => $this->maskSensitiveHeaders($request->headers->all()),
        ]);
        $response = $next($request);
        \Log::info('LogRequestsMiddleware: Response generated', [
            'status' => $response->status(),
            'response_body' => $this->maskSensitiveData($response->original ?? null),
            'response_headers' => $response->headers->all(),
        ]);
        LogRequest::create([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'controller' => $this->getController($request),
            'controller_method' => $this->getControllerMethod($request),
            'request_body' => json_encode($this->maskSensitiveData($request->all())),
            'request_headers' => json_encode($this->maskSensitiveHeaders($request->headers->all())),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'response_status' => $response->status(),
            'response_body' => json_encode($this->maskSensitiveData($response->original ?? null)),
            'response_headers' => json_encode($response->headers->all()),
            'created_at' => now(),
        ]);
        return $response;
    }
    private function maskSensitiveData($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => &$value) {
                if (in_array(Str::lower($key), ['password', 'token'])) {
                    $value = Str::repeat('*', strlen((string) $value));
                } elseif (is_array($value) || is_object($value)) {
                    $value = $this->maskSensitiveData($value);
                }
            }
            return $data;
        }
        return $data;
    }
    private function maskSensitiveHeaders(array $headers): array
    {
        foreach ($headers as $key => &$value) {
            if (in_array(Str::lower($key), ['authorization', 'x-auth-token'])) {
                $value = array_map(fn($v) => Str::repeat('*', strlen($v)), $value);
            }
        }
        return $headers;
    }
    private function getController(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) return null;
        $action = $route->getAction();
        return $action['controller'] ?? null;
    }
    private function getControllerMethod(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) return null;
        $action = $route->getAction();
        return $action['uses'] ?? null;
    }
}