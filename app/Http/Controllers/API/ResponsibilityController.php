<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Responsibility;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        // Get Single Data
        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility Found');
            }

            return ResponseFormatter::error('Responsibility Not Found', 404);
        }

        // get multiple data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success($responsibilities->paginate($limit), 'Responsibilities Found');
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            // create responsibilities
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // get Responsibility
            $responsibility = Responsibility::find($id);

            // check role exists
            if (!$responsibility) {
                throw new Exception('Responsibility not Found');
            }

            // Delete Responsibility
            $responsibility->delete();

            return ResponseFormatter::success($responsibility, 'Responsibility Deleted');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
