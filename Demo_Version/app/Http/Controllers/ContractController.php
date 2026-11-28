<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorContract;
use App\Models\Employee;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function store(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $validated = $request->validate([
            'first_contract_date' => 'nullable|date',
            'job_classification' => 'required|in:إدارية,فنية,أكاديمية',
            'contract_number' => 'nullable|string|max:100',
            'wage_system' => 'required|in:شهري,بالساعة',
            'approved_wage' => 'required|numeric|min:0',
            'weekly_hours' => 'nullable|integer|min:1|max:80',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
        ]);

        Contractor::updateOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'first_contract_date' => $validated['first_contract_date'] ?? $validated['contract_start_date'],
                'job_classification' => $validated['job_classification'],
            ]
        );

        $isExpired = !empty($validated['contract_end_date']) && strtotime($validated['contract_end_date']) < strtotime(date('Y-m-d'));

        ContractorContract::create([
            'employee_id' => $employee->employee_id,
            'contract_number' => $validated['contract_number'] ?? ('CTR-' . date('Y') . '-' . rand(100, 999)),
            'wage_system' => $validated['wage_system'],
            'approved_wage' => $validated['approved_wage'],
            'weekly_hours' => $validated['weekly_hours'] ?? null,
            'contract_start_date' => $validated['contract_start_date'],
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'contract_status' => $isExpired ? 'منتهٍ' : 'ساري المفعول',
            'created_at' => now(),
        ]);

        return redirect()->route('employees.show', [$employee->employee_id, 'tab' => 'contracts'])
            ->with('success', 'تم إبرام وحفظ عقد المتعاقد بنجاح.');
    }

    public function printPdf($employeeId, $contractId)
    {
        $employee = Employee::with(['currentEmployment.organizationalUnit', 'contractor'])->findOrFail($employeeId);
        $contract = ContractorContract::where('employee_id', $employeeId)->where('contract_id', $contractId)->firstOrFail();

        return view('employees.contract_pdf', compact('employee', 'contract'));
    }
}
