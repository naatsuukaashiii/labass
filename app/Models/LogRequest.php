<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LogRequest extends Model
{
    protected $table = 'logs_requests';
    protected $fillable = [
        'method',
        'url',
        'controller',
        'controller_method',
        'request_body',
        'request_headers',
        'user_id',
        'ip_address',
        'user_agent',
        'response_status',
        'response_body',
        'response_headers',
        'created_at',
    ];
    protected $casts = [
        'request_body' => 'array',
        'request_headers' => 'array',
        'response_body' => 'array',
        'response_headers' => 'array',
        'created_at' => 'datetime',
    ];
}