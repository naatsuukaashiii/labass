<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\LogRequest;
use App\DTO\LogRequestCollectionDTO;
use App\DTO\LogRequestDTO;
class LogRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-list-log-request')) {
            return response()->json(['message' => 'Permission denied: get-list-log-request'], 403);
        }
        $sortBy = $request->input('sortBy', []);
        $filter = $request->input('filter', []);
        $page = $request->input('page', 1);
        $count = $request->input('count', 10);
        $query = LogRequest::query();
        foreach ($filter as $item) {
            $key = $item['key'];
            $value = $item['value'];
            if (in_array($key, ['user_id', 'response_status', 'ip_address', 'user_agent', 'controller'])) {
                $query->where($key, '=', $value);
            }
        }
        foreach ($sortBy as $item) {
            $key = $item['key'];
            $order = $item['order'] === 'asc' ? 'asc' : 'desc';
            $query->orderBy($key, $order);
        }
        $logs = $query->paginate($count, ['*'], 'page', $page);
        $dto = new LogRequestCollectionDTO(
            $logs->map(function ($log) {
                return [
                    'url' => $log->url,
                    'controller' => $log->controller,
                    'controller_method' => $log->controller_method,
                    'response_status' => $log->response_status,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            })->toArray()
        );
        $response = [
            'data' => $dto->toArray(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ],
        ];
        return response()->json($response);
    }
    public function show($id): JsonResponse
    {
        if (!auth()->user()->hasPermission('read-log-request')) {
            return response()->json(['message' => 'Permission denied: read-log-request'], 403);
        }
        $log = LogRequest::find($id);
        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }
        $dto = new LogRequestDTO(
            id: $log->id,
            method: $log->method,
            url: $log->url,
            controller: $log->controller,
            controller_method: $log->controller_method,
            request_body: json_decode($log->request_body, true),
            request_headers: json_decode($log->request_headers, true),
            user_id: $log->user_id,
            ip_address: $log->ip_address,
            user_agent: $log->user_agent,
            response_status: $log->response_status,
            response_body: json_decode($log->response_body, true),
            response_headers: json_decode($log->response_headers, true),
            created_at: $log->created_at->toDateTimeString()
        );
        return response()->json($dto);
    }
    public function destroy($id): JsonResponse
    {
        if (!auth()->user()->hasPermission('delete-log-request')) {
            return response()->json(['message' => 'Permission denied: delete-log-request'], 403);
        }
        $log = LogRequest::find($id);
        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }
        $log->delete();
        return response()->json(['message' => 'Log deleted successfully']);
    }
}