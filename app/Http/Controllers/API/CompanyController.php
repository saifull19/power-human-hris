<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        if ($id) {
            // mengambil sigle data company sesuai user yang login

            // $company = Company::whereHas('users', function ($query) {
            //     $query->where('user_id', Auth::id());
            // })->with(['users'])->find($id);  //pakai mode ruwet

            $company = $companyQuery->find($id);        // cara simple

            if ($company) {
                return ResponseFormatter::success($company, 'Company Found');
            }

            return ResponseFormatter::error('Company Not Found');
        }

        // mengambil semua data
        // $companies = Company::with(['users']);

        // mengambil multiple data compeny berdasarkan user id yang sedang login

        // $companies = Company::with(['users'])->whereHas('users', function ($query) {
        //     $query->where('user_id', Auth::id());
        // });          //pakai cara ruwet

        $companies = $companyQuery;   // Cara Simple

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        // kalo ditulis secara langsung jadinya seperti ini
        // Company::with(['users'])->where('name', '%nama field%')->paginate(10);

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies Found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            // upload LOGO
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            // create COMPANY
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            if (!$company) {
                throw new Exception('Company Not Created');
            }

            // attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // load users at company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // get company
            $company = Company::find($id);

            // check if company exist
            if (!$company) {
                throw new Exception('Company not Found');
            }

            // update logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // update Company
            $company->update([
                'name' => $request->name,
                'logo' => $path
            ]);

            return ResponseFormatter::success($company, 'Company Updated');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
