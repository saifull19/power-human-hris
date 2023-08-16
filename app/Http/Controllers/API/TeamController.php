<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        // get single data
        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }

            return ResponseFormatter::error('Team Not Found', 404);
        }

        // get multiple data
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' | $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams Found'
        );
    }


    public function create(CreateTeamRequest $request)
    {
        try {
            // upload icon
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Create icon
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if (!$team) {
                throw new Exception('Company Not Created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            // Get Team
            $team = Team::find($id);

            // check if team exist
            if (!$team) {
                throw new Exception('Team not Found');
            }

            // Upload Logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Update Team
            $team->update([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // get team
            $team = Team::find($id);

            // check if team exist
            if (!$team) {
                throw new Exception('Team Not Found');
            }

            // Delete Team
            $team->delete();

            return ResponseFormatter::success('Team Deleted');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
