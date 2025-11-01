<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'dept_id';
    protected $fillable = ['dept_name'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'dept_id');
    }

    // CRUD methods using stored procedures
    public static function createDepartment($deptName)
    {
        return DB::select('SELECT create_department(?) as result', [$deptName])[0]->result;
    }

    public static function getDepartment($deptId)
    {
        return DB::select('SELECT get_department(?) as result', [$deptId])[0]->result;
    }

    public static function updateDepartment($deptId, $deptName)
    {
        return DB::select('SELECT update_department(?, ?) as result', [$deptId, $deptName])[0]->result;
    }

    public static function deleteDepartment($deptId)
    {
        return DB::select('SELECT delete_department(?) as result', [$deptId])[0]->result;
    }
}