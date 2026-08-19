<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\Approver;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\CompanyPolicy;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\Holiday;
use App\Models\Intern;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataApiController extends Controller
{
    public function companies(): JsonResponse
    {
        $companies = Company::with(['locations', 'policy'])->get();

        return response()->json(['data' => $companies]);
    }

    public function showCompany(int $id): JsonResponse
    {
        $company = Company::with(['locations', 'policy'])->findOrFail($id);

        return response()->json(['data' => $company]);
    }

    public function companyLocations(Request $request): JsonResponse
    {
        $query = CompanyLocation::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function showCompanyLocation(int $id): JsonResponse
    {
        $location = CompanyLocation::with('company')->findOrFail($id);

        return response()->json(['data' => $location]);
    }

    public function companyPolicies(Request $request): JsonResponse
    {
        $query = CompanyPolicy::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function showCompanyPolicy(int $id): JsonResponse
    {
        $policy = CompanyPolicy::with('company')->findOrFail($id);

        return response()->json(['data' => $policy]);
    }

    public function divisions(Request $request): JsonResponse
    {
        $query = Division::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function showDivision(int $id): JsonResponse
    {
        $division = Division::with('company')->findOrFail($id);

        return response()->json(['data' => $division]);
    }

    public function jobTitles(Request $request): JsonResponse
    {
        $query = JobTitle::with('division');

        if ($request->has('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function showJobTitle(int $id): JsonResponse
    {
        $jobTitle = JobTitle::with('division')->findOrFail($id);

        return response()->json(['data' => $jobTitle]);
    }

    public function jobLevels(): JsonResponse
    {
        return response()->json(['data' => JobLevel::all()]);
    }

    public function showJobLevel(int $id): JsonResponse
    {
        $jobLevel = JobLevel::findOrFail($id);

        return response()->json(['data' => $jobLevel]);
    }

    public function employees(Request $request): JsonResponse
    {
        $query = Employee::with(['company', 'division', 'jobTitle', 'jobLevel', 'user']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        if ($request->has('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $employees = $query->paginate(20);

        return response()->json(['data' => $employees]);
    }

    public function showEmployee(int $id): JsonResponse
    {
        $employee = Employee::with(['company', 'division', 'jobTitle', 'jobLevel', 'user'])->findOrFail($id);

        return response()->json(['data' => $employee]);
    }

    public function interns(Request $request): JsonResponse
    {
        $query = Intern::with(['company', 'division', 'mentor', 'user']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        $interns = $query->paginate(20);

        return response()->json(['data' => $interns]);
    }

    public function showIntern(int $id): JsonResponse
    {
        $intern = Intern::with(['company', 'division', 'mentor', 'user'])->findOrFail($id);

        return response()->json(['data' => $intern]);
    }

    public function freelancers(Request $request): JsonResponse
    {
        $query = Freelancer::with(['company', 'division', 'supervisor', 'user']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        $freelancers = $query->paginate(20);

        return response()->json(['data' => $freelancers]);
    }

    public function showFreelancer(int $id): JsonResponse
    {
        $freelancer = Freelancer::with(['company', 'division', 'supervisor', 'user'])->findOrFail($id);

        return response()->json(['data' => $freelancer]);
    }

    public function holidays(Request $request): JsonResponse
    {
        $query = Holiday::query();

        if ($request->has('year')) {
            $query->whereYear('date', $request->input('year'));
        }

        return response()->json(['data' => $query->orderBy('date')->get()]);
    }

    public function showHoliday(int $id): JsonResponse
    {
        $holiday = Holiday::findOrFail($id);

        return response()->json(['data' => $holiday]);
    }

    public function approvalFlows(Request $request): JsonResponse
    {
        $query = ApprovalFlow::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        if ($request->has('request_type')) {
            $query->where('request_type', $request->input('request_type'));
        }

        return response()->json(['data' => $query->orderBy('company_id')->orderBy('request_type')->orderBy('step_order')->get()]);
    }

    public function showApprovalFlow(int $id): JsonResponse
    {
        $flow = ApprovalFlow::with('company')->findOrFail($id);

        return response()->json(['data' => $flow]);
    }

    public function approvers(Request $request): JsonResponse
    {
        $query = Approver::with(['user', 'company', 'division']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        if ($request->has('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function showApprover(int $id): JsonResponse
    {
        $approver = Approver::with(['user', 'company', 'division'])->findOrFail($id);

        return response()->json(['data' => $approver]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'employee', 'intern', 'freelancer']);

        if ($request->has('email')) {
            $query->where('email', 'like', '%'.$request->input('email').'%');
        }

        $users = $query->paginate(20);

        return response()->json(['data' => $users]);
    }

    public function showUser(int $id): JsonResponse
    {
        $user = User::with(['roles', 'permissions', 'employee', 'intern', 'freelancer'])->findOrFail($id);

        return response()->json(['data' => $user]);
    }
}
