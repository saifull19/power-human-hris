<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Contracts\Routing\ResponseFactory;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $gender = $request->input('gender');
        $phone = $request->input('phone');
        $role_id = $request->input('role_id');
        $team_id = $request->input('team_id');
        $limit = $request->input('limit', 10);

        $employeeQuery = Employee::query();

        // get single data
        if ($id) {
            $employee = $employeeQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee Found');
            }

            return ResponseFormatter::error('Employee Not Found', 404);
        }

        // get multiple data
        $employees = $employeeQuery;

        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $employees->where('email', $email);
        }

        if ($age) {
            $employees->where('age', $age);
        }

        if ($gender) {
            $employees->where('gender', $gender);
        }

        if ($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }

        if ($team_id) {
            $employees->where('team_id', $team_id);
        }

        if ($role_id) {
            $employees->where('role_id', $role_id);
        }

        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees Found'
        );
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            // upload photos
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Create Employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'age' => $request->age,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            if (!$employee) {
                throw new Exception('Employee Not Created');
            }

            return ResponseFormatter::success($employee, 'Employee Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            // get Employee
            $employee = Employee::find($id);

            // check is Employee Exists
            if (!$employee) {
                throw new Exception('Employee Not Found');
            }

            // Upload Photo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // update Employee
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'age' => $request->age,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // get Employee
            $employee = Employee::find($id);

            // check is Employee Exists
            if (!$employee) {
                throw new Exception('Employee Not Found');
            }

            // Delete Employee
            $employee->delete();

            return ResponseFormatter::success('Employe Deleted');
        } catch (Exception $th) {
            return responseFormatter::error($th->getMessage(), 500);
        }
    }
}
