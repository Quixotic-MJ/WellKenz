<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'emp_id';
    protected $fillable = [
        'emp_name', 
        'emp_position', 
        'emp_email', 
        'emp_contact', 
        'dept_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'emp_id');
    }

    // CRUD methods using stored procedures
    public static function createEmployee($data)
    {
        return DB::select('SELECT create_employee(?, ?, ?, ?, ?) as result', [
            $data['emp_name'],
            $data['emp_position'],
            $data['emp_email'],
            $data['emp_contact'],
            $data['dept_id']
        ])[0]->result;
    }

    public static function getEmployee($empId)
    {
        return DB::select('SELECT get_employee(?) as result', [$empId])[0]->result;
    }

    public static function updateEmployee($empId, $data)
    {
        return DB::select('SELECT update_employee(?, ?, ?, ?, ?, ?) as result', [
            $empId,
            $data['emp_name'],
            $data['emp_position'],
            $data['emp_email'],
            $data['emp_contact'],
            $data['dept_id']
        ])[0]->result;
    }

    public static function deleteEmployee($empId)
    {
        return DB::select('SELECT delete_employee(?) as result', [$empId])[0]->result;
    }
}