# واجهة الدخول والتحقق (UserForm0)
### في ملف: `ibbunvAkademy.xlsm`

---

## 📌 نبذة عامة
واجهة مصغرة تظهر تلقائياً عند فتح ملف الإكسل (Triggered by `Workbook_Open`). وظيفتها حماية المصنف بكلمة مرور قبل السماح للمستخدم برؤية القوائم وأوراق العمل.

---

## 🎨 عناصر التحكم في الواجهة (Form Controls):

| اسم الأداة (Control Name) | نوع الأداة (Type) | العنوان / النص (Caption / Text) | الوظيفة البرمجية |
| :--- | :--- | :--- | :--- |
| `TextBox1` | مربع نصي (TextBox) | - (Password Mask) | إدخال كلمة المرور (الافتراضية: `123`). |
| `CommandButton1` | زر أمر (Button) | **دخول** | التحقق من صحة كلمة المرور وفتح الشاشة الرئيسية. |
| `CommandButton2` | زر أمر (Button) | **خروج** | إغلاق برنامج الإكسل نهائياً مع إيقاف التنبيهات. |
| `Label1` | تسمية نصية (Label) | أدخل كلمة المرور | نص إرشادي للمستخدم. |

---

## ⚙️ آلية العمل والمنطق البرمجي (VBA Logic):

```vba
Private Sub CommandButton1_Click()
    TextBox1.SetFocus
    If TextBox1.Value = 123 Then
        Unload Me
        MenuForm.Show  ' فتح القائمة الرئيسية فور التحقق
    Else
        MsgBox "كلمت المرور غير صحيحة حاول مرة آخرى", vbInformation, "تحذير"
        TextBox1.Value = ""
        TextBox1.SetFocus
    End If
End Sub

Private Sub CommandButton2_Click()
    If CloseMode = vbFormControlMenu Then Cancel = True
    MsgBox " !!! سوف يتم اغلاق البرنامج نهائياً "
    Application.DisplayAlerts = False
    Application.Quit
End Sub
```

---

## 🔄 المقابل المعماري في موقع الويب (Web Modernization):
* استبدال كلمة المرور الثابتة والسطحية (`123`) بنظام **مصادقة قياسي (JWT / Session Authentication)** مع تشفير `Bcrypt`، وتوزيع الأدوار (مدير نظام، موظف أرشيف، عميد كلية).
