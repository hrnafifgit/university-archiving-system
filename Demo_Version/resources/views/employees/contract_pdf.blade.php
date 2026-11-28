<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نموذج عقد متعاقد — {{ $employee->full_name_ar }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: #0f172a; margin: 28px; line-height: 1.9; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px solid #14532d; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #14532d; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #334155; padding: 8px 10px; text-align: right; }
        th { background: #f1f5f9; width: 26%; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; }
        .active { background: #dcfce7; color: #166534; }
        .expired { background: #fee2e2; color: #991b1b; }
        .signs { display: flex; justify-content: space-between; margin-top: 48px; text-align: center; }
        .signs div { width: 30%; }
        @media print { .no-print { display: none; } body { margin: 12px; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 14px; display:flex; gap:8px;">
        <button onclick="window.print()" style="background:#14532d;color:#fff;border:none;padding:8px 18px;border-radius:8px;cursor:pointer;font-weight:bold;">طباعة / حفظ PDF</button>
        <button onclick="window.close()" style="background:#e2e8f0;color:#1e293b;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;">إغلاق</button>
    </div>

    <div class="header">
        <h1>الجمهورية اليمنية — جامعة إب</h1>
        <p>نموذج عقد عمل متعاقد ({{ $contract->wage_system ?? 'شهري' }})</p>
    </div>

    <p>إنه في يوم <strong>{{ $contract->contract_start_date?->format('Y/m/d') ?? date('Y/m/d') }}</strong> تم الاتفاق بين <strong>جامعة إب</strong> (الطرف الأول) والسيد/ة <strong>{{ $employee->full_name_ar }}</strong> (الطرف الثاني) على العمل وفق البيانات والشروط التالية:</p>

    <table>
        <tr><th>اسم المتعاقد</th><td>{{ $employee->full_name_ar }}</td><th>رقم العقد</th><td>{{ $contract->contract_number ?? '—' }}</td></tr>
        <tr><th>الرقم الوطني / البطاقة</th><td>{{ $employee->national_id ?? '—' }}</td><th>تاريخ التعاقد الأول</th><td>{{ $employee->contractor?->first_contract_date?->format('Y/m/d') ?? $contract->contract_start_date?->format('Y/m/d') }}</td></tr>
        <tr><th>الجهة / الكلية</th><td>{{ $employee->currentEmployment?->organizationalUnit?->unit_name_ar ?? 'جامعة إب' }}</td><th>تصنيف الوظيفة</th><td>{{ $employee->contractor?->job_classification ?? 'إدارية' }}</td></tr>
        <tr><th>نظام الأجر</th><td>{{ $contract->wage_system ?? 'شهري' }}</td><th>الأجر المعتمد</th><td>{{ number_format((float)$contract->approved_wage, 0) }} ريال</td></tr>
        <tr><th>تاريخ بداية العقد</th><td>{{ $contract->contract_start_date?->format('Y/m/d') ?? '—' }}</td><th>تاريخ انتهاء العقد</th><td>{{ $contract->contract_end_date?->format('Y/m/d') ?? 'مفتوح' }}</td></tr>
        <tr><th>الساعات الأسبوعية</th><td>{{ $contract->weekly_hours ?? '—' }}</td><th>حالة العقد</th><td><span class="badge {{ $contract->isExpired() ? 'expired' : 'active' }}">{{ $contract->computed_status }}</span></td></tr>
    </table>

    <p>يلتزم الطرف الثاني بأداء المهام الموكلة إليه في الجهة المحددة أعلاه، وتُصرف مستحقاته المالية وفق نظام الأجر المعتمد وبعد استيفاء المراجعة والاعتماد الرسمي.</p>

    <div class="signs">
        <div><strong>الطرف الثاني (المتعاقد)</strong><br><br>التوقيع: ......................</div>
        <div><strong>مدير عام الموارد البشرية / الشؤون الأكاديمية</strong><br><br>التوقيع: ......................</div>
        <div><strong>رئيس الجامعة / الأمين العام</strong><br><br>التوقيع: ......................</div>
    </div>
</body>
</html>
