<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $roleQuery = Role::query();

        // Get Single Data
        if ($id) {
            $role = $roleQuery->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role Found');
            }

            return ResponseFormatter::error('Role not Found', 404);
        }

        // Get Multiple Data
        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles Found'
        );
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            // create Role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if (!$role) {
                throw new Exception('Role Not Created');
            }

            return ResponseFormatter::success($role, 'Role Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            // Get Role
            $role = Role::find($id);

            // check Role Exists
            if (!$role) {
                throw new Exception('Role Not Found');
            }

            // update Role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            return ResponseFormatter::success($role, 'Role Updated');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // get Role
            $role = Role::find($id);

            // check Role Exists
            if (!$role) {
                throw new Exception('Role Not Found');
            }

            // Delete Role
            $role->delete();

            return ResponseFormatter::success($role, 'Role Deleted');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
