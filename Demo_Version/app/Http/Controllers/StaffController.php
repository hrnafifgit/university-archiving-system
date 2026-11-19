<?php

namespace App\Http\Controllers;

use App\Models\AcademicDetail;
use App\Models\AcademicRank;
use App\Models\AdministrativeDecision;
use App\Models\AdministrativeDetail;
use App\Models\ContractDetail;
use App\Models\DepartmentUnit;
use App\Models\DigitalArchive;
use App\Models\JobGrade;
use App\Models\OrganizationalEntity;
use App\Models\Qualification;
use App\Models\StaffBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    /**
     * القوائم المنسدلة المستخرجة من نظام الإكسل السابق (Lookups from Excel)
     */
    public static function getExcelLookups(): array
    {
        return [
            'jobStatuses' => [
                'مثبت',
                'موفد بالخارج',
                'موفد بالداخل',
                'إجازة تفرغ علمي',
                'إجازة تفرغ براتب',
                'إجازة بدون راتب',
                'منتدب',
                'مهمة وطنية',
                'مفرغ للدراسة',
                'خفض وإضافة',
                'إحلال',
                'بدل',
                'بلغ أجل التقاعد',
                'منقول من الجامعة',
                'مريض',
                'منقطع',
            ],
            'degrees' => [
                'دكتوراه',
                'دكتوراه مهنية',
                'ماجستير',
                'ماجستير مهني',
                'بورد',
                'بكالوريوس',
                'دبلوم عالي',
            ],
            'universities' => [
                'جامعة إب',
                'جامعة صنعاء',
                'جامعة عدن',
                'جامعة تعز',
                'جامعة القاهرة',
                'جامعة أسيوط',
                'جامعة عين شمس',
                'جامعة الملك سعود',
                'جامعة بغداد',
                'جامعة دمشق',
                'جامعة ميسور (الهند)',
                'جامعة العلوم والتكنولوجيا',
            ],
            'countries' => [
                'اليمن',
                'مصر',
                'السعودية',
                'الهند',
                'ماليزيا',
                'الأردن',
                'العراق',
                'سوريا',
                'روسيا',
                'ألمانيا',
            ],
            'specializations' => [
                'علوم الحاسوب',
                'هندسة البرمجيات',
                'تقنية معلومات وشبكات',
                'طب وجراحة الأسنان',
                'تقويم الأسنان',
                'جراحة الفم والفكين',
                'فقه مقارن وأصول فقه',
                'علوم القرآن والدراسات الإسلامية',
                'لغة عربية ودراسات لغوية',
                'لغة إنجليزية وأدب',
                'جغرافيا طبيعية',
                'كيمياء كهربية وتحليلية',
                'كيمياء عضوية',
                'فيزياء الجوامد',
                'رياضيات وإحصاء',
                'أحياء دقيقة (ميكروبيولوجي)',
                'علوم الحياة (البيولوجي)',
                'إدارة أعمال',
                'محاسبة ومراجعة',
                'علوم مالية ومصرفية',
                'مناهج وطرق تدريس',
                'إرشاد نفسي وتربوي',
                'إنتاج نباتي',
                'إنتاج حيواني',
                'علوم وتكنولوجيا الأغذية',
            ],
            'decisionTypes' => [
                'قرار تعيين رسمي',
                'قرار ترقية علمية',
                'قرار تثبيت وظيفي',
                'قرار إيفاد للدراسة',
                'قرار تفرغ علمي',
                'قرار تكليف إداري',
                'قرار إجازة بدون راتب',
                'قرار نقل أو ندب',
            ],
            'adminJobTitles' => [
                'عميد كلية / مركز',
                'نائب العميد للشؤون الأكاديمية',
                'نائب العميد لشؤون الطلبة',
                'نائب العميد للدراسات العليا',
                'رئيس قسم',
                'مسجل عام / مسجل كلية',
                'أمين كلية',
                'مدير عام إدارة',
                'أخصائي شؤون أكاديمية',
                'أخصائي مالي وإداري',
                'أخصائي تقني وفني',
                'موظف مكتبي',
            ],
            'contractTypes' => [
                'ساعات فصلي',
                'ساعات سنوي',
                'عقد سنوي مقطوع',
                'عقد استشاري',
            ],
        ];
    }

    /**
     * دليل واستعلام الكادر المتعدد المعايير (مطابق لآلية واجهة bisho و form_Cm_Search)
     */
    public function index(Request $request)
    {
        $query = StaffBase::with([
            'department.entity',
            'academicDetails.rank',
            'administrativeDetails.jobGrade',
            'contractDetails',
            'qualifications',
            'archives'
        ]);

        // 1. البحث النصي السريع (الاسم، الرقم الوظيفي، الرقم الوطني، الهاتف)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('job_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 2. تصفية الكلية / الجهة العليا (Level 1)
        if ($request->filled('entity_id')) {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('entity_id', $request->entity_id);
            });
        }

        // 3. تصفية القسم العلمي (Level 2)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 4. تصفية نوع الكادر (Subtype)
        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }

        // 5. تصفية الرتبة العلمية (Academic Rank)
        if ($request->filled('rank_id')) {
            $query->whereHas('academicDetails', function ($q) use ($request) {
                $q->where('rank_id', $request->rank_id);
            });
        }

        // 6. تصفية الحالة الوظيفية (مثبت / مفرغ / منتدب / إجازة)
        if ($request->filled('job_status')) {
            $query->where('job_status', $request->job_status);
        }

        // 7. تصفية النوع الاجتماعي (الجنس)
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // 8. تصفية الحالة الأكاديمية
        if ($request->filled('academic_status')) {
            $query->whereHas('academicDetails', function ($q) use ($request) {
                $q->where('academic_status', $request->academic_status);
            });
        }

        // 9. تصفية الدرجة الوظيفية المالية
        if ($request->filled('job_grade_id')) {
            $query->whereHas('administrativeDetails', function ($q) use ($request) {
                $q->where('job_grade_id', $request->job_grade_id);
            });
        }

        // 10. تصفية المؤهل العلمي
        if ($request->filled('degree_type')) {
            $query->whereHas('qualifications', function ($q) use ($request) {
                $q->where('degree_type', 'like', "%{$request->degree_type}%");
            });
        }

        // إحصائيات العدّادات اللحظية المتطابقة مع عدّادات bisho (TxtCount1 to TxtCount8)
        $totalMatched = (clone $query)->count();
        $academicCount = (clone $query)->where('staff_type', 'academic')->count();
        $adminCount = (clone $query)->where('staff_type', 'administrative')->count();
        $contractCount = (clone $query)->where('staff_type', 'contract')->count();
        $activeCount = (clone $query)->where('job_status', 'مثبت')->count();
        $phdCount = (clone $query)->whereHas('qualifications', function ($q) {
            $q->where('degree_type', 'like', '%دكتوراه%');
        })->count();

        // التصفح والتنظيم
        $staffList = $query->latest()->paginate(12)->withQueryString();

        // قوائم الفلترة المنسدلة المستندة لبيانات الإكسل
        $entities = OrganizationalEntity::with('departments')->orderBy('name')->get();
        $departments = DepartmentUnit::with('entity')->orderBy('name')->get();
        $ranks = AcademicRank::orderBy('seniority_order')->get();
        $grades = JobGrade::all();
        $lookups = self::getExcelLookups();

        return view('staff.index', compact(
            'staffList',
            'entities',
            'departments',
            'ranks',
            'grades',
            'lookups',
            'totalMatched',
            'academicCount',
            'adminCount',
            'contractCount',
            'activeCount',
            'phdCount'
        ));
    }

    /**
     * واجهة إدخال كادر جديد
     */
    public function create()
    {
        $entities = OrganizationalEntity::with('departments')->orderBy('name')->get();
        $departments = DepartmentUnit::with('entity')->orderBy('name')->get();
        $ranks = AcademicRank::orderBy('seniority_order')->get();
        $grades = JobGrade::all();
        $lookups = self::getExcelLookups();

        return view('staff.create', compact('entities', 'departments', 'ranks', 'grades', 'lookups'));
    }

    /**
     * حفظ وتخزين الكادر الجديد الفعلي في قاعدة البيانات (3NF Transaction)
     */
    public function store(Request $request)
    {
        // 1. التحقق من صحة المدخلات
        $validated = $request->validate([
            'department_id' => 'required|exists:departments_units,id',
            'job_number' => 'required|string|unique:staff_base,job_number|max:50',
            'national_id' => 'required|string|unique:staff_base,national_id|max:50',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:ذكر,أنثى',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'hire_date' => 'nullable|date',
            'staff_type' => 'required|in:academic,administrative,contract',
            'job_status' => 'required|string|max:50',
            // ملف الوثيقة (PDF)
            'document_file' => 'nullable|file|mimes:pdf|max:20480', // حتى 20 ميجابايت
            'document_title' => 'nullable|string|max:255',
            'tree_category' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // 2. إدراج السجل في جدول الأساس المشترك (staff_base)
            $staff = StaffBase::create([
                'department_id' => $validated['department_id'],
                'job_number' => $validated['job_number'],
                'national_id' => $validated['national_id'],
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'hire_date' => $validated['hire_date'] ?? now()->toDateString(),
                'staff_type' => $validated['staff_type'],
                'job_status' => $validated['job_status'],
            ]);

            // 3. التخصيص والوراثة حسب نوع الكادر (1:1 Subtypes)
            if ($validated['staff_type'] === 'academic') {
                AcademicDetail::create([
                    'staff_id' => $staff->id,
                    'rank_id' => $request->input('rank_id', 1),
                    'general_specialization' => $request->input('general_specialization', 'علوم حاسوب'),
                    'exact_specialization' => $request->input('exact_specialization', 'هندسة برمجيات'),
                    'academic_status' => $request->input('academic_status', 'مستمر'),
                    'teaching_load' => $request->input('teaching_load', 12),
                    'last_promotion_date' => $request->input('last_promotion_date', null),
                ]);

                // إدراج مؤهل أكاديمي إذا توفر
                if ($request->filled('degree_type')) {
                    Qualification::create([
                        'staff_id' => $staff->id,
                        'degree_type' => $request->input('degree_type'),
                        'specialization' => $request->input('qual_specialization', 'تخصص علمي'),
                        'granting_institution' => $request->input('granting_institution', 'جامعة إب'),
                        'graduation_year' => $request->input('graduation_year', date('Y')),
                        'country' => $request->input('country', 'اليمن'),
                        'thesis_title' => $request->input('thesis_title', null),
                    ]);
                }
            } elseif ($validated['staff_type'] === 'administrative') {
                AdministrativeDetail::create([
                    'staff_id' => $staff->id,
                    'job_grade_id' => $request->input('job_grade_id', 1),
                    'job_title' => $request->input('job_title', 'موظف إداري'),
                    'work_nature' => $request->input('work_nature', 'مكتبي'),
                    'admin_confirmation_date' => $request->input('admin_confirmation_date', null),
                    'performance_rating' => $request->input('performance_rating', 'ممتاز'),
                ]);
            } elseif ($validated['staff_type'] === 'contract') {
                ContractDetail::create([
                    'staff_id' => $staff->id,
                    'contract_type' => $request->input('contract_type', 'ساعات فصلي'),
                    'approved_hours' => $request->input('approved_hours', 8),
                    'hourly_rate' => $request->input('hourly_rate', 5000),
                    'contract_start_date' => $request->input('contract_start_date', now()->toDateString()),
                    'contract_end_date' => $request->input('contract_end_date', null),
                ]);
            }

            // 4. معالجة وتخزين ملف الـ PDF المرفوع (الأرشفة الرقمية الحقيقية)
            $decisionId = null;
            if ($request->filled('decision_number')) {
                $decision = AdministrativeDecision::create([
                    'staff_id' => $staff->id,
                    'decision_number' => $request->input('decision_number'),
                    'decision_type' => $request->input('decision_type', 'قرار تعيين'),
                    'issued_date' => $request->input('issued_date', now()->toDateString()),
                    'effective_date' => $request->input('effective_date', now()->toDateString()),
                    'status' => 'ساري',
                    'notes' => $request->input('decision_notes', 'تم إصدار القرار بموجب اللائحة التنفيذية.'),
                ]);
                $decisionId = $decision->id;
            }

            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $uuid = (string) Str::uuid();
                $cleanFileName = $uuid . '.pdf';
                $path = $file->storeAs('archives/' . $staff->job_number, $cleanFileName, 'public');

                DigitalArchive::create([
                    'uuid' => $uuid,
                    'staff_id' => $staff->id,
                    'decision_id' => $decisionId,
                    'qualification_id' => null,
                    'document_title' => $request->input('document_title', 'وثيقة رسمية مؤرخة'),
                    'tree_category' => $request->input('tree_category', 'القرارات الإدارية'),
                    'file_path' => $path,
                    'file_size_bytes' => $file->getSize(),
                    'page_count' => 1,
                    'mime_type' => 'application/pdf',
                ]);
            } else {
                // إذا لم يرفع ملفاً، نربط له افتراضياً إحدى وثائق الأرشيف التجريبية المنسوخة
                $uuid = (string) Str::uuid();
                DigitalArchive::create([
                    'uuid' => $uuid,
                    'staff_id' => $staff->id,
                    'decision_id' => $decisionId,
                    'qualification_id' => null,
                    'document_title' => 'قرار التعيين والاعتماد الأكاديمي الرسمي',
                    'tree_category' => 'القرارات الإدارية',
                    'file_path' => 'archives/1.PDF',
                    'file_size_bytes' => 249427,
                    'page_count' => 1,
                    'mime_type' => 'application/pdf',
                ]);
            }

            DB::commit();

            return redirect()->route('staff.show', $staff->id)
                             ->with('success', 'تم حفظ وتخزين بيانات الكادر بنجاح في قاعدة البيانات وتوليد وثائق الأرشيف!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage());
        }
    }

    /**
     * الملف الموحد وعارض الأرشيف الشجري المدمج (360° Profile)
     */
    public function show($id)
    {
        $staff = StaffBase::with([
            'department.entity',
            'academicDetails.rank',
            'administrativeDetails.jobGrade',
            'contractDetails',
            'qualifications',
            'decisions.archives',
            'archives'
        ])->findOrFail($id);

        return view('staff.profile', compact('staff'));
    }

    /**
     * واجهة تعديل بيانات الكادر القائم (Edit Form)
     */
    public function edit($id)
    {
        $staff = StaffBase::with([
            'department.entity',
            'academicDetails.rank',
            'administrativeDetails.jobGrade',
            'contractDetails',
            'qualifications',
            'decisions',
            'archives'
        ])->findOrFail($id);

        $entities = OrganizationalEntity::with('departments')->orderBy('name')->get();
        $departments = DepartmentUnit::with('entity')->orderBy('name')->get();
        $ranks = AcademicRank::orderBy('seniority_order')->get();
        $grades = JobGrade::all();
        $lookups = self::getExcelLookups();

        return view('staff.edit', compact('staff', 'entities', 'departments', 'ranks', 'grades', 'lookups'));
    }

    /**
     * حفظ وتحديث بيانات الكادر في قاعدة البيانات (Update)
     */
    public function update(Request $request, $id)
    {
        $staff = StaffBase::findOrFail($id);

        // 1. التحقق من صحة المدخلات مع استثناء معرف الكادر الحالي
        $validated = $request->validate([
            'department_id' => 'required|exists:departments_units,id',
            'job_number' => 'required|string|max:50|unique:staff_base,job_number,' . $staff->id,
            'national_id' => 'required|string|max:50|unique:staff_base,national_id,' . $staff->id,
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:ذكر,أنثى',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'hire_date' => 'nullable|date',
            'staff_type' => 'required|in:academic,administrative,contract',
            'job_status' => 'required|string|max:50',
            'document_file' => 'nullable|file|mimes:pdf|max:20480',
            'document_title' => 'nullable|string|max:255',
            'tree_category' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // 2. تحديث جدول الأساس المشترك (staff_base)
            $staff->update([
                'department_id' => $validated['department_id'],
                'job_number' => $validated['job_number'],
                'national_id' => $validated['national_id'],
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'hire_date' => $validated['hire_date'] ?? $staff->hire_date,
                'staff_type' => $validated['staff_type'],
                'job_status' => $validated['job_status'],
            ]);

            // 3. تحديث تفاصيل الفئة (Subtypes)
            if ($validated['staff_type'] === 'academic') {
                AcademicDetail::updateOrCreate(
                    ['staff_id' => $staff->id],
                    [
                        'rank_id' => $request->input('rank_id', 1),
                        'general_specialization' => $request->input('general_specialization', 'علوم حاسوب'),
                        'exact_specialization' => $request->input('exact_specialization', 'هندسة برمجيات'),
                        'academic_status' => $request->input('academic_status', 'مستمر'),
                        'teaching_load' => $request->input('teaching_load', 12),
                        'last_promotion_date' => $request->input('last_promotion_date', null),
                    ]
                );
            } elseif ($validated['staff_type'] === 'administrative') {
                AdministrativeDetail::updateOrCreate(
                    ['staff_id' => $staff->id],
                    [
                        'job_grade_id' => $request->input('job_grade_id', 1),
                        'job_title' => $request->input('job_title', 'موظف إداري'),
                        'work_nature' => $request->input('work_nature', 'مكتبي'),
                        'admin_confirmation_date' => $request->input('admin_confirmation_date', null),
                        'performance_rating' => $request->input('performance_rating', 'ممتاز'),
                    ]
                );
            } elseif ($validated['staff_type'] === 'contract') {
                ContractDetail::updateOrCreate(
                    ['staff_id' => $staff->id],
                    [
                        'contract_type' => $request->input('contract_type', 'ساعات فصلي'),
                        'approved_hours' => $request->input('approved_hours', 8),
                        'hourly_rate' => $request->input('hourly_rate', 5000),
                        'contract_start_date' => $request->input('contract_start_date', now()->toDateString()),
                        'contract_end_date' => $request->input('contract_end_date', null),
                    ]
                );
            }

            // 4. إذا تم رفع ملف PDF جديد يتم حفظه في الأرشيف
            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $uuid = (string) Str::uuid();
                $cleanFileName = $uuid . '.pdf';
                $path = $file->storeAs('archives/' . $staff->job_number, $cleanFileName, 'public');

                DigitalArchive::create([
                    'uuid' => $uuid,
                    'staff_id' => $staff->id,
                    'decision_id' => null,
                    'qualification_id' => null,
                    'document_title' => $request->input('document_title', 'وثيقة أرشيف إضافية معدلة'),
                    'tree_category' => $request->input('tree_category', 'القرارات الإدارية'),
                    'file_path' => $path,
                    'file_size_bytes' => $file->getSize(),
                    'page_count' => 1,
                    'mime_type' => 'application/pdf',
                ]);
            }

            DB::commit();

            return redirect()->route('staff.show', $staff->id)
                             ->with('success', 'تم تحديث بيانات الكادر بنجاح وحفظ التغييرات في قاعدة البيانات!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تعديل البيانات: ' . $e->getMessage());
        }
    }

    /**
     * حذف كادر من قاعدة البيانات
     */
    public function destroy($id)
    {
        $staff = StaffBase::findOrFail($id);
        $staff->delete();

        return redirect()->route('staff.index')
                         ->with('success', 'تم حذف سجل الكادر ومستنداته بنجاح!');
    }
}
