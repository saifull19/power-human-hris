<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\ResponseFormatter;
use App\Http\Requests\CreateCompanyRequest;
use App\Models\Company;

class CompanyController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        if ($id) {
            $company = Company::with(['users'])->find($id);  //pakai relasi

            if ($company) {
                return ResponseFormatter::success($company, 'Company Found');
            }

            return ResponseFormatter::error('Company Not Found');
        }

        $companies = Company::with(['users']);

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
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/logos');
        }
        $company = Company::create([
            'name' => $request->name,
            'logo' => $path
        ]);

        return ResponseFormatter::success($company, 'Company Created');
    }
}
