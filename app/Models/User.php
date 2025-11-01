<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'username',
        'password',
        'role',
        'emp_id'
    ];

    protected $hidden = ['password'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // CRUD methods using stored procedures
    public static function createUser($data)
    {
        return DB::select('SELECT create_user(?, ?, ?, ?) as result', [
            $data['username'],
            $data['password'],
            $data['role'],
            $data['emp_id']
        ])[0]->result;
    }

    public static function getUser($userId)
    {
        return DB::select('SELECT get_user(?) as result', [$userId])[0]->result;
    }

    public static function authenticate($username, $password)
    {
        return DB::select('SELECT authenticate_user(?, ?) as result', [$username, $password])[0]->result;
    }

    public static function updateUser($userId, $data)
    {
        return DB::select('SELECT update_user(?, ?, ?, ?) as result', [
            $userId,
            $data['username'],
            $data['role'],
            $data['emp_id']
        ])[0]->result;
    }

    public static function changePassword($userId, $newPassword)
    {
        return DB::select('SELECT change_user_password(?, ?) as result', [$userId, $newPassword])[0]->result;
    }

    public static function deleteUser($userId)
    {
        return DB::select('SELECT delete_user(?) as result', [$userId])[0]->result;
    }
}