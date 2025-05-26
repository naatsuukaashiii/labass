<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;
    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
        'created_by',
        'deleted_by',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
    ];
    protected $hidden = [ 
        'password',
        'remember_token',
        'two_factor_code',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_expires_at' => 'datetime',
    ];
    protected $dates = ['deleted_at', 'two_factor_expires_at'];
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_and_roles')
            ->withPivot('created_at', 'created_by', 'deleted_at', 'deleted_by')
            ->withTimestamps();
    }
    public function hasPermission($permissionCode)
    {
        return $this->roles->flatMap(fn($role) => $role->permissions)->pluck('code')->contains($permissionCode);
    }
    public function enableTwoFactorAuth()
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }
    public function disableTwoFactorAuth()
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }
    public function generateTwoFactorCode()
    {
        $code = rand(100000, 999999);
        $expirationMinutes = (int)config('app.two_factor_code_expiration');
        $this->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes($expirationMinutes),
            'two_factor_attempts' => 0,
        ]);
        return $code;
    }
    public function invalidateTwoFactorCode()
    {
        $this->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }
    public function isTwoFactorCodeValid($code)
    {
        return $this->two_factor_code === $code && now()->lessThan($this->two_factor_expires_at);
    }
}