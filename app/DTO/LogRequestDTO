<?php
namespace App\DTO;
class LogRequestDTO
{
    public function __construct(
        public int $id,
        public string $method,
        public string $url,
        public ?string $controller,
        public ?string $controller_method,
        public ?array $request_body,
        public ?array $request_headers,
        public ?int $user_id,
        public string $ip_address,
        public ?string $user_agent,
        public int $response_status,
        public ?array $response_body,
        public ?array $response_headers,
        public string $created_at
    ) {}
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'url' => $this->url,
            'controller' => $this->controller,
            'controller_method' => $this->controller_method,
            'request_body' => $this->request_body,
            'request_headers' => $this->request_headers,
            'user_id' => $this->user_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'response_status' => $this->response_status,
            'response_body' => $this->response_body,
            'response_headers' => $this->response_headers,
            'created_at' => $this->created_at,
        ];
    }
}