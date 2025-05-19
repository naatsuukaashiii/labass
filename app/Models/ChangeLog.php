<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ChangeLog extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'entity_type',
        'entity_id',
        'before',
        'after',
    ];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}