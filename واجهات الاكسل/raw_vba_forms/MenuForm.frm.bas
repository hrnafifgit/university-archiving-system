Attribute VB_Name = "MenuForm"
Attribute VB_Base = "0{2F5D9D21-790D-4FDD-9DBC-22473080B142}{78091C84-5A34-4F04-8F2B-B6DD4B0C836C}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False


' 1. زر فتح فورم إدخال وتعديل البيانات
Private Sub CommandButton1_Click()
  On Error Resume Next
    Me.Hide ' إخفاء قائمة التحكم مؤقتاً
     Application.Visible = True
Sheets("Feuil1").Activate
Call kh_Show_UFormChang
'    UFormChang9.Show ' فتح فورم الإدخال
    Me.Show ' إعادة إظهار القائمة بعد إغلاق الفورم الآخر
'On Error Resume Next
On Error GoTo 0
End Sub

Private Sub CommandButton10_Click()
On Error Resume Next
 Me.Hide
   Application.Visible = True
    'Unload Me
    Sheets("prnn").Activate
    'Sheets("sheet2").Select
On Error GoTo 0
End Sub

Private Sub CommandButton11_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("star1").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton12_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh1").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton13_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh3").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton14_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh2").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton15_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh4").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton16_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh5").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton17_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh6").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton18_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh7").Activate
On Error GoTo 0
End Sub

Private Sub CommandButton19_Click()
On Error Resume Next
Me.Hide
Application.Visible = True
Sheets("eh8").Activate
On Error GoTo 0
End Sub

' 2. زر فتح فورم البحث والتصفية والترحيل
Private Sub CommandButton2_Click()
  
On Error Resume Next

 Me.Hide
 Application.Visible = True
Sheets("Feuil1").Activate
bisho.Show

  '  UserForm22.Show
    ' Me.Show
    On Error GoTo 0
   
 
End Sub

Private Sub CommandButton20_Click()
On Error Resume Next
        With Application
           .DisplayFullScreen = False
                ActiveWindow.DisplayHeadings = True
              ActiveWorkbook.Application.DisplayFormulaBar = True
           .CommandBars("MyToolbar").Enabled = False
           .CommandBars("Worksheet Menu Bar").Enabled = False
            .CommandBars("standerbar").Enabled = False
            Application.WindowState = xlMaximized
        End With
    On Error GoTo 0
End Sub

Private Sub CommandButton21_Click()
 On Error Resume Next
        With Application
           .DisplayFullScreen = True
           ActiveWorkbook.Application.DisplayFormulaBar = True
                    .CommandBars("MyToolbar").Enabled = True
           .CommandBars("Worksheet Menu Bar").Enabled = True
           .CommandBars("standerbar").Enabled = True
           ActiveWindow.DisplayHeadings = False
           Application.WindowState = xlMaximized
        End With
    On Error GoTo 0
End Sub

Private Sub CommandButton22_Click()
 On Error Resume Next
ActiveWorkbook.save
On Error GoTo 0
End Sub

Private Sub CommandButton23_Click()
 On Error Resume Next
Application.Quit
On Error GoTo 0
End Sub

' 3. زر إغلاق القائمة بالكامل
Private Sub CommandButton3_Click()
  On Error Resume Next
   Application.Visible = True
    Unload Me
    Sheets("star1").Activate
 '   Sheets("star1").Select
       On Error GoTo 0
End Sub

Private Sub CommandButton4_Click()
  On Error Resume Next
    
   ' Me.Hide
   ' UserForm3.Show ' فتح فورم الإحصائيات الذكي
   ' Me.Show
    On Error GoTo 0
End Sub

Private Sub CommandButton6_Click()
   On Error Resume Next

 Me.Hide
    ReportForm.Show
    Me.Show
    On Error GoTo 0
End Sub

Private Sub CommandButton7_Click()
   On Error Resume Next

 Me.Hide
 Application.Visible = True
Sheets("Feuil1").Activate
    UserForm66.Show
    Me.Show
    On Error GoTo 0
End Sub

Private Sub CommandButton8_Click()
Unload Me
Application.Visible = True
'Application.DisplayFullScreen = True
'ActiveWindow.DisplayHeadings = False
'Application.DisplayFormulaBar = False

On Error Resume Next
        With Application
           .DisplayFullScreen = True
           ActiveWorkbook.Application.DisplayFormulaBar = True
                    .CommandBars("MyToolbar").Enabled = False
           .CommandBars("Worksheet Menu Bar").Enabled = True
           .CommandBars("standerbar").Enabled = True
           ActiveWindow.DisplayHeadings = False
           Application.WindowState = xlMaximized
        End With
    
Sheets("star1").Activate
On Error GoTo 0


End Sub



Private Sub CommandButton9_Click()
On Error Resume Next
    Me.Hide ' إخفاء قائمة التحكم مؤقتاً
     Application.Visible = True
Sheets("Feuil1").Activate
Call kh_Show_UFormChang3
'    UFormChang9.Show ' فتح فورم الإدخال
    Me.Show ' إعادة إظهار القائمة بعد إغلاق الفورم الآخر
'On Error Resume Next
On Error GoTo 0
End Sub

' كود جمالي لتوسيط الفورم تلقائياً في منتصف الشاشة عند الفتح
Private Sub UserForm_Initialize()
      On Error Resume Next

    Me.StartUpPosition = 2
    
    t10.Text = Format(Now, "hh:mm:ss AM/PM")
    t11.Text = Format(Now, "yyyy/mm/dd")
  '  t12.Text = Format(Now, "YYYY/MM/DD") & " هـ"
    
'   t10.Text = Format(Date, "YYYY/MM/DD")
'    t11.Text = Format(Time())
    On Error GoTo 0
End Sub

