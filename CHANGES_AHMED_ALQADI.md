# سجل التعديلات والتحديثات — فرع `ahmed-alqadi`

هذا الملف يوثق جميع التعديلات والإضافات البرمجية التي تم تنفيذها على المشروع بعد دمج تحديثات الفرع `demo-version-2`، وذلك لتسهيل مراجعة مسؤول المشروع وفريق التطوير.

---

## 1. تحسينات شاشة إضافة الكادر (`employees/create`)
- **التحقق الإلزامي خطوة بخطوة (Strict Step-by-Step Validation):**
  - منع الانتقال من أي مرحلة إلى المرحلة التالية (عبر زر "التالي" أو عبر تبويبات المعالج الخمسة) قبل استكمال الحقول الأساسية المطلوبة في المرحلة الحالية.
  - إظهار تنبيه عائم واضح مع تمييز الحقل الناقص بالإطار الأحمر وتوجيه التركيز (`focus`) إليه مباشرة.
- **معالجة إرسال النموذج متعدد الخطوات:**
  - إضافة `novalidate` للنموذج وربط أزرار الحفظ بدالة موحدة `submitStaffForm()` تتحقق من كافة المراحل الخمس وتوجه المستخدم لأي حقل ناقص قبل الإرسال، مما منع الإيقاف الصامت الذي تسببه المتصفحات مع الحقول المخفية.
- **الترقيم الوظيفي التلقائي ومنع التكرار:**
  - توليد الرقم الوظيفي التسلسلي تلقائياً في `EmployeeController@create` (مع ضمان توليده في `store` إن تُرك فارغاً) وإلزام تعبئته قبل الانتقال للخطوة الثانية.
  - إضافة فحص لحظي (Live AJAX Duplicate Check) عبر المسار `/employees/check-duplicate` للتحقق من عدم تكرار **الرقم الوظيفي** و**الرقم الوطني / البطاقة** أثناء الكتابة.
  - تقييد الحقول الرقمية (الرقم الوظيفي، الرقم الوطني، الهاتف، نصاب الساعات، أجر الساعة، رقم القرار) لقبول الأرقام والصيغ الصحيحة فقط.
- **تحويل حقول كشف الراتب والمؤهلات إلى قوائم منسدلة (Comboboxes) مع أزرار إضافة سريعة (`+`):**
  - تحويل الحقول التالية إلى قوائم منسدلة متصلة بنافذة الإضافة السريعة (`openQuickAdd`) دون فقدان بيانات النموذج:
    - الحالة بكشف الراتب (`payroll_status`)
    - الوظيفة بكشف الراتب (`payroll_job_title`) — مع مسار `POST /quick-add/job-title`
    - الدرجة بكشف الراتب (`payroll_job_grade`)
    - المؤهل عند التعيين بالجامعة (`qualification_at_appointment`)
    - الدرجة المعين فيها أول مرة (`initial_rank`)
    - الجامعة المانحة للمؤهل (`university_name`) — مع مسار `POST /quick-add/university`
  - توحيد أزرار `+` بجانب جميع القوائم المنسدلة في خطوات المعالج (الكلية، القسم، الموقف، الرتبة، الدرجة، المؤهل، نوع الوثيقة).

---

## 2. تحسينات ملف الكادر الموحد (`employees/show`)
- **إخفاء بطاقة رفع وأرشفة الملف افتراضياً:**
  - عند فتح ملف أي كادر، تُعرض بياناته بعرض الشاشة الكامل (`lg:col-span-12`) لتجربة قراءة مريحة.
  - إضافة زر **«رفع وأرشفة ملف»** في الشريط العلوي (يظهر فقط لمن يملك صلاحية `archive.create`) لإظهار/إخفاء البطاقة عند الحاجة، مع زر إغلاق (`×`) داخل ترويسة البطاقة.

---

## 3. تطوير وتصحيح نظام المستخدمين والأدوار والصلاحيات (RBAC)
- **مواءمة مصفوفة الصلاحيات مع شاشات النظام الفعلية (`PermissionService` & `roles_matrix`):**
  - استبدال الشاشات الوهمية/القديمة بالشاشات الـ 8 الحقيقية للنظام مع مسميات مطابقة للقائمة الجانبية:
    1. **الشاشة الرئيسية** (`dashboard`: عرض)
    2. **شاشة الكادر** (`employees`: عرض، إضافة، تعديل، حذف)
    3. **شاشة القرارات** (`decisions`: عرض، إضافة، تعديل، حذف)
    4. **شاشة الأرشيف** (`archive`: عرض، إضافة، تعديل، حذف)
    5. **شاشة الهيكل** (`org_units`: عرض، إضافة، تعديل، حذف)
    6. **شاشة التقارير** (`reports`: عرض، تصدير)
    7. **شاشة الاعتمادات** (`workflow`: عرض، إضافة، اعتماد)
    8. **شاشة الصلاحيات** (`system_settings`: عرض، إضافة، تعديل، حذف)
  - إظهار مربعات الاختيار (Checkboxes) للعمليات المنطبقة على كل شاشة فقط ووضع `—` للعمليات غير المنطبقة.
- **إدارة المستخدمين ومنع التكرار (`RolePermissionController`):**
  - تنظيف المستخدمين التجريبيين والإبقاء على الحساب الرئيسي (`admin`) فقط، مع منع إعادة بذر مستخدمين عشوائيين داخل `PermissionService::ensureSeeded()`.
  - منع تكرار اسم المستخدم (بصيغة موحدة غير حساسة لحالة الأحرف) ومنع ربط نفس الموظف بأكثر من حساب، مع إضافة فحص لحظي لتوفر اسم المستخدم عبر `/security/users/check-username`.
  - إضافة إمكانية حذف المستخدمين الفرعيين مع حماية الحساب الرئيسي `admin`.
- **توجيه الدخول والتزامن اللحظي للصلاحيات (`AuthController` & `EnsureDesktopAuthenticated`):**
  - توجيه المستخدم بعد تسجيل الدخول تلقائياً إلى أول شاشة يملك صلاحية عرضها (`PermissionService::getLandingRoute()`).
  - إضافة صفحة آمنة (`/no-permissions`) للمستخدمين الذين لا يملكون أي صلاحية عرض لمنع حدوث حلقة إعادة توجيه (`ERR_TOO_MANY_REDIRECTS`).
  - تحديث الصلاحيات والدور تلقائياً ولحظياً من قاعدة البيانات في كل طلب عبر `EnsureDesktopAuthenticated`، بحيث تسري تعديلات المدير فور تحديث الصفحة (F5) دون الحاجة لتسجيل الخروج.
- **الحماية الصارمة للمسارات والواجهات (`routes/web.php` & Blade Views):**
  - حماية كافة مسارات العرض والإضافة والتعديل والحذف والتصدير والاعتماد بوسيط `EnsureHasPermission` وفق الصلاحيات التفصيلية.
  - تصفية روابط القائمة الجانبية في `layouts/app.blade.php` وإخفاء أزرار (الإضافة، التعديل، الحذف، الاستيراد، الرفع، الاستعادة) في شاشات **الكادر**، **ملف الكادر**، **القرارات**، **الأرشيف**، و**الهيكل** عن أي مستخدم لا يملك صلاحيتها.

---

## 4. أبرز الملفات المعدلة والمضافة
- `Demo_Version/app/Http/Controllers/EmployeeController.php`
- `Demo_Version/app/Http/Controllers/QuickAddController.php`
- `Demo_Version/app/Http/Controllers/AuthController.php`
- `Demo_Version/app/Http/Controllers/RolePermissionController.php`
- `Demo_Version/app/Http/Middleware/EnsureDesktopAuthenticated.php`
- `Demo_Version/app/Http/Middleware/EnsureHasPermission.php`
- `Demo_Version/app/Services/PermissionService.php`
- `Demo_Version/routes/web.php`
- `Demo_Version/resources/views/layouts/app.blade.php`
- `Demo_Version/resources/views/employees/create.blade.php`
- `Demo_Version/resources/views/employees/index.blade.php`
- `Demo_Version/resources/views/employees/show.blade.php`
- `Demo_Version/resources/views/decisions/index.blade.php`
- `Demo_Version/resources/views/archive/index.blade.php`
- `Demo_Version/resources/views/master_data/index.blade.php`
- `Demo_Version/resources/views/security/roles_matrix.blade.php`
- `Demo_Version/resources/views/auth/no_permissions.blade.php` *(ملف جديد)*
