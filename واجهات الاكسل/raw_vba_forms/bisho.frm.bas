Attribute VB_Name = "bisho"
Attribute VB_Base = "0{D12DCFAC-2AA9-4F18-99B2-747BD8096CF8}{14EE8450-9B9C-460B-AFEB-773725CA7553}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False
Dim wsMain As Worksheet, wsSet As Worksheet, wsTarget As Worksheet
Dim isUpdating As Boolean ' لمنع التداخل اللانهائي أثناء تحديث القوائم



Private Sub UserForm_Initialize()
    On Error Resume Next
    Set wsMain = Sheets("Feuil1")
    Set wsSet = Sheets("seting1")
    Set wsTarget = Sheets("Sheet2")
    
    ' جلب التسميات ديناميكياً من ورقة seting الصف الأول
    Label1.Caption = wsSet.Range("A3").Value
    Label2.Caption = wsSet.Range("B3").Value
    Label3.Caption = wsSet.Range("C3").Value
    Label4.Caption = wsSet.Range("D3").Value
    Label5.Caption = wsSet.Range("E3").Value
    Label6.Caption = wsSet.Range("F3").Value
    Label7.Caption = wsSet.Range("G3").Value
    Label8.Caption = wsSet.Range("L3").Value
   ' On Error Resume Next
    On Error GoTo 0
    
    ' تنشيط الشيت الرئيسي وتفعيل التصفية التلقائية إن لم تكن مفعلة
    wsMain.Activate
    If Not wsMain.AutoFilterMode Then
          wsMain.Range("A5:AR5").AutoFilter
    Else
        ' إلغاء أي تصفية قديمة كانت معلقة في الشيت لتبدأ الواجهة نظيفة
        If wsMain.FilterMode Then wsMain.ShowAllData
    End If
    
    ' تشغيل شحن القوائم وحساب الأعداد الإجمالية لكل الموظفين فوراً عند الفتح
    Call UpdateAllCombosAndCounts
End Sub

' =========================================================================
' --- تصحيح أحداث التغيير الفورية للفلاتر وعزلها عن أخطاء السيرفر البرمجي ---
' =========================================================================

Private Sub ComboBox1_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox2_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox3_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox4_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox5_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox6_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub


Private Sub ComboBox7_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub

Private Sub ComboBox8_Change()
    If Not isUpdating Then
        Call ApplyLiveFilter
        Call UpdateAllCombosAndCounts
    End If
End Sub




' =========================================================================

Sub ApplyLiveFilter()
    ' تطبيق التصفية الفورية على أعمدة الشيت الرئيسي (3 ، 5 ، 8 ، 16، 17، 18، 19)
     On Error Resume Next
    If ComboBox1.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=3, Criteria1:=ComboBox1.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=3
    End If
    If ComboBox2.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=5, Criteria1:=ComboBox2.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=5
    End If
    
    If ComboBox3.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=8, Criteria1:=ComboBox3.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=8
    End If
    
    If ComboBox4.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=16, Criteria1:=ComboBox4.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=16
    End If
    
    If ComboBox5.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=17, Criteria1:=ComboBox5.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=17
    End If
    
    If ComboBox6.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=18, Criteria1:=ComboBox6.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=18
    End If
    
    If ComboBox7.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=19, Criteria1:=ComboBox7.Text
    Else
        wsMain.Range("A5:AR1500").AutoFilter Field:=19
    End If
    
     If ComboBox8.Text <> "" Then
        wsMain.Range("A5:AR1500").AutoFilter Field:=44, Criteria1:=ComboBox8.Text
    Else
     On Error Resume Next
         wsMain.Range("A5:AR1500").AutoFilter Field:=44
    End If
        
    
End Sub

Sub UpdateAllCombosAndCounts()
    ' تعبئة القوائم المنسدلة وحساب أعداد الموظفين بناءً على الصفوف الظاهرة فقط
    isUpdating = True
    Application.ScreenUpdating = False
    
    Dim LastRow As Long, R As Long
    LastRow = wsMain.Cells(wsMain.Rows.Count, "D").End(xlUp).Row
    If LastRow < 6 Then LastRow = 6
    
    ' إنشاء مجموعات برمجية (Collections) لمنع تكرار القيم في القوائم المفلترة
    Dim col1 As New Collection, col2 As New Collection, col3 As New Collection
    Dim col4 As New Collection, col5 As New Collection, col6 As New Collection
    Dim col7 As New Collection, col8 As New Collection
    
    ' عدادات منفصلة لكل معيار
    Dim cnt1 As Long, cnt2 As Long, cnt3 As Long, cnt4 As Long, cnt5 As Long, cnt6 As Long, cnt7 As Long, cnt8 As Long
    
    ' حفظ القيم المختارة الحالية حتى لا يمسحها الكود أثناء التحديث والتصفية المتتابعة
    Dim s1 As String, s2 As String, s3 As String, s4 As String, s5 As String, s6 As String, s7 As String, s8 As String
    s1 = ComboBox1.Text: s2 = ComboBox2.Text: s3 = ComboBox3.Text
    s4 = ComboBox4.Text: s5 = ComboBox5.Text: s6 = ComboBox6.Text
    s7 = ComboBox7.Text: s8 = ComboBox8.Text
    
    On Error Resume Next
    For R = 6 To LastRow
        ' التحقق مما إذا كان الصف ظاهراً (غير مخفي بالتصفية) ونجمع القيم بشكل ديناميكي مترابط
        If wsMain.Rows(R).Hidden = False Then
            ' 1. النوع (العمود 5)
            If Trim(wsMain.Cells(R, 3).Value) <> "" Then
                col1.Add Trim(wsMain.Cells(R, 3).Value), key:=Trim(wsMain.Cells(R, 3).Value)
                If s1 <> "" And Trim(wsMain.Cells(R, 3).Value) = s1 Then cnt1 = cnt1 + 1 Else If s1 = "" Then cnt1 = cnt1 + 1
            End If
            
               ' 2. (الموقف الوظيفي)
            If Trim(wsMain.Cells(R, 5).Value) <> "" Then
                col2.Add Trim(wsMain.Cells(R, 5).Value), key:=Trim(wsMain.Cells(R, 5).Value)
                If s2 <> "" And Trim(wsMain.Cells(R, 5).Value) = s2 Then cnt2 = cnt2 + 1 Else If s2 = "" Then cnt2 = cnt2 + 1
            End If
                     ' 6. حالة الموظف (العمود 10)
            If Trim(wsMain.Cells(R, 8).Value) <> "" Then
                col3.Add Trim(wsMain.Cells(R, 8).Value), key:=Trim(wsMain.Cells(R, 8).Value)
                If s3 <> "" And Trim(wsMain.Cells(R, 8).Value) = s3 Then cnt3 = cnt3 + 1 Else If s3 = "" Then cnt3 = cnt3 + 1
            End If
                                         
            ' 2. الجهة (العمود 6)
            If Trim(wsMain.Cells(R, 16).Value) <> "" Then
                col4.Add Trim(wsMain.Cells(R, 16).Value), key:=Trim(wsMain.Cells(R, 16).Value)
                If s4 <> "" And Trim(wsMain.Cells(R, 16).Value) = s4 Then cnt4 = cnt4 + 1 Else If s4 = "" Then cnt4 = cnt4 + 1
            End If
            ' 3. الإدارة (العمود 7)
            If Trim(wsMain.Cells(R, 17).Value) <> "" Then
                col5.Add Trim(wsMain.Cells(R, 17).Value), key:=Trim(wsMain.Cells(R, 17).Value)
                If s5 <> "" And Trim(wsMain.Cells(R, 17).Value) = s5 Then cnt5 = cnt5 + 1 Else If s5 = "" Then cnt5 = cnt5 + 1
            End If
            ' 4. الدرجة الوظيفية (العمود 8)
            If Trim(wsMain.Cells(R, 18).Value) <> "" Then
                col6.Add Trim(wsMain.Cells(R, 18).Value), key:=Trim(wsMain.Cells(R, 18).Value)
                If s6 <> "" And Trim(wsMain.Cells(R, 18).Value) = s6 Then cnt4 = cnt6 + 1 Else If s6 = "" Then cnt6 = cnt6 + 1
            End If
            ' 5. الوظيفة (العمود 9)
            If Trim(wsMain.Cells(R, 19).Value) <> "" Then
                col7.Add Trim(wsMain.Cells(R, 19).Value), key:=Trim(wsMain.Cells(R, 19).Value)
                If s7 <> "" And Trim(wsMain.Cells(R, 19).Value) = s7 Then cnt7 = cnt7 + 1 Else If s7 = "" Then cnt7 = cnt7 + 1
            End If
            
            ' 5. الوظيفة (العمود 9)
            If Trim(wsMain.Cells(R, 44).Value) <> "" Then
                col8.Add Trim(wsMain.Cells(R, 44).Value), key:=Trim(wsMain.Cells(R, 44).Value)
                If s8 <> "" And Trim(wsMain.Cells(R, 44).Value) = s8 Then cnt8 = cnt8 + 1 Else If s8 = "" Then cnt8 = cnt8 + 1
            End If
        End If
    Next R
    On Error GoTo 0
    
    ' إعادة شحن الكومبوبوكسات بالقيم المترابطة الفريدة فقط إذا كان الكومبوبوكس غير محدد بقيمة
    Dim item As Variant
    If s1 = "" Then
        ComboBox1.Clear
        For Each item In col1: ComboBox1.AddItem item: Next item
    End If
     
    If s2 = "" Then
        ComboBox2.Clear
        For Each item In col2: ComboBox2.AddItem item: Next item
    End If
    If s3 = "" Then
        ComboBox3.Clear
        For Each item In col3: ComboBox3.AddItem item: Next item
    End If
    If s4 = "" Then
        ComboBox4.Clear
        For Each item In col4: ComboBox4.AddItem item: Next item
    End If
    If s5 = "" Then
        ComboBox5.Clear
        For Each item In col5: ComboBox5.AddItem item: Next item
    End If
    If s6 = "" Then
        ComboBox6.Clear
        For Each item In col6: ComboBox6.AddItem item: Next item
    End If
    If s7 = "" Then
        ComboBox7.Clear
        For Each item In col7: ComboBox7.AddItem item: Next item
    End If
       If s8 = "" Then
        ComboBox8.Clear
        For Each item In col8: ComboBox8.AddItem item: Next item
    End If
    
    
    ' إرجاع القيم المحددة مسبقاً لمكانها لضمان استمرار التصفية المتراكمة
    If s1 <> "" Then ComboBox1.Text = s1
    If s2 <> "" Then ComboBox2.Text = s2
    If s3 <> "" Then ComboBox3.Text = s3
    If s4 <> "" Then ComboBox4.Text = s4
    If s5 <> "" Then ComboBox5.Text = s5
    If s6 <> "" Then ComboBox6.Text = s6
    If s7 <> "" Then ComboBox7.Text = s7
    If s8 <> "" Then ComboBox8.Text = s8
    ' عرض الأعداد الإحصائية الحية داخل مربعات النصوص (TextBoxes) المخصصة لها
    TxtCount1.Text = cnt1
    TxtCount2.Text = cnt2
    TxtCount3.Text = cnt3
    TxtCount4.Text = cnt4
    TxtCount5.Text = cnt5
    TxtCount6.Text = cnt6
    TxtCount7.Text = cnt7
    TxtCount8.Text = cnt8
    
    Application.ScreenUpdating = True
    isUpdating = False
End Sub

' 1. زر مسح المعايير وإلغاء التصفية بالكامل (CommandButton1)
Private Sub CommandButton1_Click()
   On Error Resume Next
    isUpdating = True
    ComboBox1.ListIndex = -1: ComboBox2.ListIndex = -1: ComboBox3.ListIndex = -1
    ComboBox4.ListIndex = -1: ComboBox5.ListIndex = -1: ComboBox6.ListIndex = -1
    ComboBox7.ListIndex = -1: ComboBox8.ListIndex = -1
    If wsMain.FilterMode Then wsMain.ShowAllData
    
    ' تنظيف مربعات النصوص
    TxtCount1.Text = "": TxtCount2.Text = "": TxtCount3.Text = ""
    TxtCount4.Text = "": TxtCount5.Text = "": TxtCount6.Text = ""
    TxtCount7.Text = "": TxtCount8.Text = ""
    
    ' تنظيف جدول الترحيل في Sheet2 والترويسة المحددة
    Dim lastTargRow As Long
    lastTargRow = wsTarget.Cells(wsTarget.Rows.Count, "A").End(xlUp).Row
    If lastTargRow >= 6 Then wsTarget.Range("A6:S" & lastTargRow).Clear
    wsTarget.Range("F1,F2,F3,H1,H2,H3,I2,j2").ClearContents
     On Error GoTo 0
    
    isUpdating = False
    Call UpdateAllCombosAndCounts
    MsgBox "تم تصفير الفلاتر وإعادة عرض إجماليات الموظفين بنجاح.", vbInformation, "تأكيد"
End Sub

' 2. زر الترحيل لـ Sheet2 (CommandButton2)
Private Sub CommandButton2_Click()
    Dim nextRow As Long
    Dim lastTargRow As Long
    lastTargRow = wsTarget.Cells(wsTarget.Rows.Count, "A").End(xlUp).Row
    If lastTargRow >= 6 Then wsTarget.Range("A6:S" & lastTargRow).Clear
    
    ' ترحيل خيارات التصفية الحالية لترويسة Sheet2
    wsTarget.Range("j2").Value = ComboBox1.Text
    wsTarget.Range("F1").Value = ComboBox2.Text
    wsTarget.Range("F2").Value = ComboBox3.Text
    wsTarget.Range("F3").Value = ComboBox4.Text
    wsTarget.Range("H1").Value = ComboBox5.Text
    wsTarget.Range("H2").Value = ComboBox6.Text
    wsTarget.Range("H3").Value = ComboBox7.Text
    wsTarget.Range("I2").Value = ComboBox8.Text
    ' نسخ ولصق الصفوف الظاهرة (المفلترة) فقط
    On Error Resume Next
    wsMain.Range("A6:S1500").SpecialCells(xlCellTypeVisible).Copy


    wsTarget.Range("A6").PasteSpecial Paste:=xlPasteValues
    wsTarget.Columns("K:M").NumberFormat = "yyyy/mm/dd"
On Error GoTo 0

Dim endRow As Long
endRow = wsTarget.Cells(wsTarget.Rows.Count, "A").End(xlUp).Row
If endRow >= 6 Then
With wsTarget.Range("A6:S" & endRow)
.Font.Name = "Arial"
.Font.Bold = True
.Font.Size = 16
.Borders.LineStyle = xlContinuous
.HorizontalAlignment = xlCenter
.VerticalAlignment = xlCenter
.RowHeight = 50
.WrapText = True
End With
MsgBox "تم ترحيل البيانات المفلترة بنجاح إلى Sheet2!", vbInformation, "تأكيد"
Else
MsgBox "لا توجد بيانات مفلترة لترحيلها حالياً!", vbExclamation, "تنبيه"
End If
Application.CutCopyMode = False
End Sub

' 3. زر معاينة الطباعة لنتائج Sheet2 (CommandButton3)
Private Sub CommandButton3_Click()
On Error Resume Next
Me.Hide
Call Preview_Sheet2
'wsTarget.Activate
'With wsTarget.PageSetup
'.Orientation = xlLandscape
'.PrintArea = "$A$1:$S$" & wsTarget.Cells(wsTarget.Rows.Count, "A").End(xlUp).Row
'.FitToPagesWide = 1
'.FitToPagesTall = False
'End With
'wsTarget.PrintOut Preview:=True
Me.Show
On Error GoTo 0
End Sub

' 4. زر إغلاق الفورم والذهاب لصفحة Sheet2 (CommandButton4)
Private Sub CommandButton4_Click()
On Error Resume Next
   Application.Visible = True
    Unload Me
    Sheets("sheet2").Activate
    Sheets("sheet2").Select
       On Error GoTo 0

'MenuForm.Show
End Sub

' 5. زر الطباعة المباشرة لنتائج Sheet2 (CommandButton5)
Private Sub CommandButton5_Click()
wsTarget.Activate
wsTarget.PrintOut
End Sub
