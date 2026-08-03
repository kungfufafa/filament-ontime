<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\Intern;
use App\Models\JobLevel;
use App\Models\JobTitle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataApiController extends Controller
{
    public function companies(): JsonResponse
    {
        $companies = Company::with('locations')->get();

        return response()->json(['data' => $companies]);
    }

    public function divisions(Request $request): JsonResponse
    {
        $query = Division::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function jobTitles(Request $request): JsonResponse
    {
        $query = JobTitle::query();

        if ($request->has('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function jobLevels(): JsonResponse
    {
        return response()->json(['data' => JobLevel::all()]);
    }

    public function employees(Request $request): JsonResponse
    {
        $query = Employee::with(['company', 'division', 'jobTitle', 'jobLevel']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $employees = $query->paginate(20);

        return response()->json(['data' => $employees]);
    }

    public function interns(Request $request): JsonResponse
    {
        $query = Intern::with(['company', 'division', 'mentor']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        $interns = $query->paginate(20);

        return response()->json(['data' => $interns]);
    }

    public function freelancers(Request $request): JsonResponse
    {
        $query = Freelancer::with(['company', 'division', 'supervisor']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        $freelancers = $query->paginate(20);

        return response()->json(['data' => $freelancers]);
    }
}
