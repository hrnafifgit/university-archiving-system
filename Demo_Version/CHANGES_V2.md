# ملخص التعديلات والتحسينات — فرع `demo-version-2`

يحتوي هذا الملف على توثيق مختصر لأبرز التعديلات والميزات الجديدة المضافة في مجلد `Demo_Version` على فرع `demo-version-2` مقارنة بالنسخة السابقة، بالإضافة إلى خطوات التشغيل المحلي.

---

## 1. واجهات إدخال وعرض بيانات الكوادر
* **تحويل شاشة إضافة كادر جديد (`resources/views/employees/create.blade.php`) إلى معالج أفقي من 5 خطوات (5-Step Wizard):**
  * إزالة التمرير العمودي الطويل واستبداله بتبويبات أفقية مع أزرار تنقل (`السابق` / `التالي`) وشريط تقدم يوضح نسبة الإنجاز.
  * توحيد التصميم الرسمي النظيف لاختيار نوع الكادر (أكاديمي / إداري / متعاقد بالساعات) وتكييف الحقول تلقائياً.
* **تنظيف وتحسين واجهات العرض والبيانات المرجعية:**
  * تحسين واجهة دليل الكوادر (`resources/views/employees/index.blade.php` و `_rows.blade.php`)، وملف الكادر (`show.blade.php`)، ونوافذ الإسناد والوثائق (`modals/new_assignment.blade.php` و `modals/new_doc.blade.php`).
  * تنظيم شاشة البيانات المرجعية (`resources/views/master_data/index.blade.php`) وربطها بـ `MasterDataController.php`.

---

## 2. لوحة المؤشرات والرسوم البيانية والتقارير
* **لوحة التحليلات التنفيذية (`AnalyticsService.php` و `resources/views/reports/partials/analytics.blade.php`):**
  * إنشاء 9 رسوم بيانية تفاعلية (Doughnut و Horizontal Bar) لتوزيع الكوادر والرتب والمؤهلات والوحدات الإدارية وحالات الراتب.
  * حصر الأشرطة الأفقية الطويلة بأعلى التصنيفات (أعلى 10 أو 12 + بند «أخرى») مع ارتفاع ديناميكي للحاويات لمنع تداخل النصوص.
* **تحسين رسوم الصفحة الرئيسية (`StatisticsService.php` و `resources/views/dashboard.blade.php`):**
  * إضافة `capMatrixRows()` لاستبعاد التصنيفات الصفرية وعرض أعلى التصنيفات + «أخرى» في الحالة الأكاديمية والمؤهلات وكشف الراتب.
  * استبدال الارتفاعات الثابتة (`h-96` / `h-64`) بارتفاع ديناميكي محسوب حسب عدد العناصر لمنع تكدّس التسميات.
* **تحديث التقارير (`ReportController.php`، `ReportMatrixService.php`، `reports/index.blade.php`، `individual.blade.php`، `matrices.blade.php`، `roster_19.blade.php`).**

---

## 3. الأرشيف والتصفية المتدرجة (Cascading Filters)
* تحديث `ArchiveController.php` و `DocumentStorageService.php` و `resources/views/archive/index.blade.php` و `preview_placeholder.blade.php` لدعم التصفية المتدرجة حسب الكلية والقسم العلمي، وتحسين معاينة الوثائق.

---

## 4. الصلاحيات (RBAC) ومسار الاعتمادات (Workflow)
* **جداول قاعدة البيانات الجديدة:**
  * إضافة الترحيل `database/migrations/2026_11_26_000001_add_rbac_security_and_workflow_tables.php` ونماذج `WorkflowStep.php` و `WorkflowTransaction.php`.
* **مصفوفة الأدوار والصلاحيات (`RolePermissionController.php`، `PermissionService.php`، `EnsureHasPermission.php`، `resources/views/security/roles_matrix.blade.php`):**
  * إدارة الأدوار والصلاحيات عبر قائمة الأدوار، مع إمكانية إضافة دور جديد وتعيين الكوادر كمستخدمين في النظام، ومعالجة مسار التحقق من الصلاحيات في `routes/web.php`.
* **سير عمل الاعتمادات التسلسلي (`ApprovalWorkflowController.php` و `resources/views/workflow/index.blade.php`):**
  * عرض خطوات الاعتماد (4 خطوات) مع مبدّل أدوار لتجربة مسار الاعتماد بين الجهات المختلفة.

---

## 5. استيراد بيانات الكادر الإداري (CLI Command)
* إضافة أمر Artisan مخصص لاستيراد بيانات الكادر الإداري من ملف Excel محلي:
  * `app/Console/Commands/ImportAdminStaffCommand.php` (`php artisan uas:import-admin-staff`).
  * *(ملاحظة: ملف الإكسل غير مرفوع إلى GitHub حفاظاً على خصوصية البيانات).*

---

## طريقة التشغيل المحلي بعد تنزيل الفرع

```bash
git clone -b demo-version-2 https://github.com/hrnafifgit/university-archiving-system.git
cd university-archiving-system/Demo_Version
composer install
cp .env.example .env
php artisan key:generate
```

بعد ضبط اتصال قاعدة بيانات PostgreSQL في ملف `.env`:

```bash
php artisan migrate --seed
php artisan serve
```

بيانات الدخول الافتراضية للتجربة المحلية:
* **اسم المستخدم:** `admin`
* **كلمة المرور:** `admin123`
