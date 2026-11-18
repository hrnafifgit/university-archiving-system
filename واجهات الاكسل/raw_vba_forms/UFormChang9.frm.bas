Attribute VB_Name = "UFormChang9"
Attribute VB_Base = "0{5AED87FE-D66A-427B-B8E6-771CB87D8462}{EE969E0B-DEEA-4090-9A4F-0D9B2803B4EA}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False






Option Explicit
'======================================================
'======================================================

'     تنسيق التاريخ
Private Const DtF As String = "yyyy/mm/dd"
'======================================================
'     عُرض تاكست الادخال
Private Const iWgt1   As Single = 200
'======================================================
Private Const Frmtop  As Single = 3
Private Const Frmlft  As Single = 3
Private Const iHgt    As Single = 22.25
Private Const iTop    As Single = iHgt + 2
Private Const mBox    As Long = vbMsgBoxRight + vbMsgBoxRtlReading
'======================================================
'======================================================
Private Ar()          As Integer
Private MyRngSeri     As Range
Private MyRngdate     As Range
Private ContRow       As Long
Private iRow          As Long
Private LastColumn    As Integer
Private tSr           As Boolean
Dim MyList            As String



Private Sub BoxFind_Click()
Dim tm As Integer
Me.ListFind.Clear
tm = Me.BoxFind.Tag
Me.Controls("Labeldt" & tm).ForeColor = vbBlack
tm = Me.BoxFind.ListIndex + 1
Me.Controls("Labeldt" & tm).ForeColor = Me.BoxFind.ForeColor
Me.BoxFind.Tag = tm
End Sub
Private Sub ButtonClear_Click()
kh_ClearRecord
End Sub

Private Sub ButtonEnd_Click()
 On Error Resume Next
     Me.ScrollBar1.Value = ContRow
     On Error GoTo 0
End Sub

Private Sub kh_ClearRecord(Optional ByVal tcler As Boolean = False)
Dim tm As Integer
''''''''''''''''''''''''''''''''
For tm = 2 To LastColumn
    If tcler Or Me.Controls("Textdt" & tm).Enabled = True Then
        Me.Controls("Textdt" & tm) = ""
    End If
Next
End Sub

Private Sub kh_AddNewRecord()
Dim C As Integer
''''''''''''''''''
On Error Resume Next
Me.Frame1.ScrollTop = 0
kh_ClearRecord True
''''''''''''''''''
Me.LabelSerial = ContRow + 1
Me.LabelSerial2 = ContRow + 1 & "  -  " & ContRow + 1
kh_Enabled False
''''''''''''''''''
On Error Resume Next
With Me.Controls("Textdt1")
    If .Enabled Then
        .SetFocus
        .Text = "يجب الادخال في هذه الخلية افتراضياً"
        .SelStart = 0
        .SelLength = .TextLength
    Else
        .Text = "........"
   On Error GoTo 0
    End If
On Error GoTo 0
End With
End Sub

Private Sub ButtonNew_Click()
kh_AddNewRecord
End Sub

Private Sub ButtonNewCancel_Click()
ScrollBar1_Change
End Sub

Private Sub ButtonNewSave_Click()
If kh_TestBlank() Then Exit Sub
Dim cRow As Long: cRow = ContRow + 1
Me.ScrollBar1.Max = cRow
kh_SaveDate cRow, True
Me.ScrollBar1.Value = cRow
Call MsgBox("  تم حفظ السجل الجديد بنجاح  ", mBox, "الحمدلله")

End Sub

Private Sub ButtonPrint_Click()

Dim C As Integer
Dim Arr

  On Error Resume Next
  
If MsgBox(" هل تريد طباعة السجل   ؟  ", vbYesNo + mBox, "طباعة  ") = vbNo Then Exit Sub

Me.Hide
'Arr = Array("D7", "D9", "J5", "D11", "D13", "R7", "R9", "R11", "E16", "K16", "R16", "B21", "B23", "B25", "B27", "B29", "B31", "B33", "B35", "B37", "R20", "R21", "R22", "R23", "R24", "R26", "R28", "R29", "R30", "O11", "R32", "R34", "R35", "R36", "R11", "B39", "R13", "M39", "M40", "U39", "U40", "U41", "F43")
Arr = Array("D7", "D9", "D11", "D13", "J5", "R7", "R9", "E16", "K16", "R16", "B21", "B23", "B25", "B27", "B29", "B31", "B33", "B35", "B37", "R20", "R21", "R22", "R23", "R24", "R26", "R28", "R29", "R30", "R32", "R34", "R35", "R36", "R11", "B39", "B40", "R13", "B41", "M39", "M40", "U39", "U40", "U41", "F43", "R37")

With Sheets("prin")

'Arr = Array("t3", "D9", "D11", "D13", "R7", "R9", "R11", "H17", "H18", "H19", "H20", "H21", "H22", "H23", "H24", "H25", "H26", "H27", "H28", "H29", "H30", "H31", "H32", "H33", "H34", "H35", "H36", "H37", "H38", "H39", "H40")

'With Sheets("prin9")
    .Range("T3").Value = iRow
    For C = 1 To LastColumn
      .Range(CStr(Arr(C - 1))).Value = CStr(Me.Controls("Textdt" & C))
    Next
    .PrintPreview
    
    On Error GoTo 0
End With
Me.Hide

End Sub

Private Sub ButtonSaveDate_Click()
If kh_TestBlank() Then Exit Sub
kh_SaveDate iRow
ScrollBar1_Change
Call MsgBox("  تم حفظ التغييرات  بنجاح  ", mBox, "الحمدلله")

End Sub

Private Function kh_TestBlank() As Boolean
If Len(Trim(Me.Controls("Textdt1"))) = 0 Then
    kh_TestBlank = True
    Me.Controls("Textdt1").SetFocus
    Call MsgBox("العمود : " & Me.Controls("Labeldt1") & vbCr & vbCr & "يجب الادخال في هذه الخلية افتراضياً", mBox + vbCritical, "استخدام خاطىء")
End If
End Function
Private Sub kh_AutoFill()
Dim CelFill As Range, CFil As Range
Dim R As Integer
''''''''''''''''''''''''''
If tSr Then
    Set CelFill = Union(MyRngSeri, MyRngdate)
Else
    Set CelFill = MyRngdate
End If
''''''''''''''''''''''''''
For R = 1 To CelFill.Areas.Count
    Set CFil = CelFill.Areas(R).Rows(ContRow + 1)
    With CFil
        .AutoFill .Resize(2), xlFillDefault
    End With
Next
Set CelFill = Nothing
Set CFil = Nothing
End Sub
Private Sub kh_SaveDate(ByVal nR As Long, Optional ByVal tFil As Boolean = False)
Dim MyVelue, Msg
Dim C As Integer, cc As Integer
''''''''''''''''''''''''''
'On Error GoTo 1
''''''''''''''''''''''''''
Application.Calculation = xlCalculationManual
''''''''''''''''''''''''''
If nR > 1 And tFil Then kh_AutoFill
If tSr Then MyRngSeri.Cells(nR + 1, 1).Value = nR
'''''''''''''''''''''''''''
For cc = 1 To LastColumn
    C = Ar(cc)
    If Me.Controls("Textdt" & cc).Enabled = True Then
        With MyRngdate
            MyVelue = Me.Controls("Textdt" & cc).Text
            If Not IsNumeric(MyVelue) And IsDate(MyVelue) Then
                MyVelue = Format(MyVelue, DtF)
            Else
                If IsNumeric(MyVelue) And IsDate(.Cells(nR + 1, C)) Then
                    Msg = MsgBox("الخلية في العمود : " & Me.Controls("Labeldt" & cc) & vbCr & vbCr _
                    & "منسقة كتاريخ والادخال الجديد رقم" & vbCr & vbCr _
                    & "هل تريد مسح تنسيقات الارقام السابقة ؟؟", mBox + vbYesNo, "تأكيد مسح تنسيقات التاريخ السابقة ؟؟  ")
                    '''''''''''''''''''''''''
                    If Msg = vbYes Then .Cells(nR + 1, C).NumberFormat = ""
                End If
            End If
            .Cells(nR + 1, C).Value = MyVelue
        End With
    End If
Next
''''''''''''''''''''''''''
1:
Application.Calculation = xlCalculationAutomatic
''''''''''''''''''''''''''

End Sub

Private Sub ButtonExit_Click()
Unload Me
MenuForm.Show
End Sub

Private Sub ButtonDelete_Click()
If MsgBox("  هل تريد حذف السجل رقم : " & iRow & vbCr & vbCr & String$(40, "="), vbCritical + vbYesNo + mBox + vbDefaultButton2, "تاكيد الحذف ") = vbNo Then Exit Sub
If Me.ListFind.ListCount Then Me.ListFind.Clear
MyRngdate.Rows(iRow + 1).EntireRow.Delete
If Not tSr Then GoTo 1
If iRow = ContRow Then GoTo 1
With MyRngSeri
    .Cells(iRow + 1, 1).Value = iRow
    Range(.Cells(iRow + 1, 1), .Cells(ContRow, 1)).DataSeries
End With
1:
Me.ScrollBar1.Max = ContRow - 1
ScrollBar1_Change
Call MsgBox("  تم حذف السجل  بنجاح  ", mBox, "الحمدلله")

End Sub

Private Sub ButtonTop_Click()
 On Error Resume Next
     If ContRow Then Me.ScrollBar1.Value = 1
      On Error GoTo 0
End Sub

Private Sub CheckFindDate_Click()
If Me.CheckFindDate.Value = True Then
    kh_SetDate Me.TextFind
    ''''''''''''''''''''''''''''
End If
End Sub
Private Sub LabelH2_Click()
    Call MsgBox(" سيتم تحويل اي قيمة تضعها في مربع النص للبحث " _
        & vbCr & vbCr & "الى تاريخ بالتنسيق الافتراضي للفورم ,,,,,,," _
        & vbCr & String$(40, "=") _
        & vbCr & vbCr & "مع امكانية ادخال رقم صحيح بين 1 الى 31 ليفهم على انه " _
        & vbCr & vbCr & "تاريخ اليوم للشهر الحالي والسنة الحالية " _
        , mBox + vbQuestion + vbApplicationModal, "تعليمات")
    '''''''''''''''''''''''''''

End Sub

Private Sub ListFind_Click()
Dim RR As Long
RR = Me.ListFind.Column(1)
Me.ScrollBar1.Value = RR
End Sub

Private Sub ScrollBar1_Change()
Dim MyVelue
Dim C As Integer, cc As Integer
 
 On Error Resume Next
     
Me.Frame1.ScrollTop = 0
With Me.ScrollBar1
 On Error Resume Next

    If ContRow = 0 Then .Min = 1
    iRow = .Value: ContRow = .Max
End With
 
     On Error GoTo 0
'''''''''''''''''
 On Error Resume Next
     For cc = 1 To LastColumn
    C = Ar(cc)
    With MyRngdate
        If IsDate(.Cells(iRow + 1, C)) Then
            MyVelue = Format(.Cells(iRow + 1, C).Value2, DtF)
            Else: MyVelue = .Cells(iRow + 1, C).Value2
        End If
         On Error GoTo 0
    End With
    On Error Resume Next
    Me.Controls("Textdt" & cc).Text = ""
    Me.Controls("Textdt" & cc).Text = MyVelue
    On Error GoTo 0
Next
'------------------------------

Me.TextSerial1.Text = iRow
Me.LabelSerial.Caption = iRow
Me.LabelSerial2.Caption = iRow & "  -  " & ContRow
kh_Enabled True
End Sub

Private Sub TextFind_Change()
With Me.ListFind
    If .ListCount Then .Clear
End With
End Sub

Private Sub TextFind_BeforeUpdate(ByVal Cancel As MSForms.ReturnBoolean)
If Me.CheckFindDate.Value = False Then Exit Sub
kh_SetDate Me.TextFind
End Sub

'  هذا الكود يرغمك بادخال تاريخ
Private Sub kh_SetDate(ByVal dCntrl As MSForms.Control)
Dim dtest, dt
dtest = dCntrl
If Not IsDate(dtest) Then
    If IsNumeric(dtest) Then
        dt = Format(DateSerial(Year(Date), Month(Date), val(dtest)), DtF)
    Else
        dt = Format(Date, DtF)
    End If
    Else: dt = Format(CDate(dtest), DtF)
End If
dCntrl = dt
End Sub
Private Sub textserial_Change()
Dim V
V = Me.TextSerial.Text
If Len(V) = 0 Then Exit Sub
If Not IsNumeric(V) Then GoTo 1
If V = 0 Or V > ContRow Then GoTo 1
'Me.TxtNumber.Text = v
Exit Sub


'=======================
1: Me.TextSerial.Text = Left(Me.TextSerial.Text, Len(Me.TextSerial.Text) - 1)
End Sub

Private Sub TextSerial_AfterUpdate()
If Len(Me.TextSerial) Then Me.ScrollBar1.Value = Me.TextSerial.Value: Me.TextSerial = ""
End Sub

Private Sub kh_Enabled(ByVal Ebl As Boolean)
Me.ButtonNewSave.Visible = Not Ebl
Me.ButtonNewCancel.Visible = Not Ebl
Me.ButtonNew.Visible = Ebl
Me.ButtonSaveDate.Visible = Ebl
''''''''''''''''''''''''''''''''''''''''''''
Me.ButtonSaveDate.Enabled = Ebl
Me.ButtonSerach.Enabled = Ebl
''''''''''''''''''''''''''''''''
Me.ButtonEnd.Enabled = CBool(iRow <> ContRow)
Me.ButtonTop.Enabled = CBool(iRow > 0 And iRow <> 1)
Me.ButtonNewCancel.Enabled = IIf(iRow, True, False)
Me.ButtonDelete.Enabled = IIf(ContRow = 1, False, Ebl)
End Sub








Private Sub BtnShowPdf_Click()
    Dim fileName As String
    Dim folderPath As String
    Dim fullPath As String
    
    fileName = Trim(Me.TxtPdfPath.Text)
    
    ' تحديد المسار الثابت للمجلد الخاص بك على القرص D
    folderPath = "D:\archac26\"
    
    ' التحقق من وجود اسم للملف في التكست بكس
    If fileName = "" Then
        MsgBox "لا يوجد اسم ملف PDF مسجل لهذا الموظف!", vbExclamation, "تنبيه"
        Exit Sub
    End If
    
    ' التأكد من وجود امتداد .pdf في نهاية اسم الملف، وإذا لم يوجد يتم إضافته تلقائياً
    If UCase(Right(fileName, 4)) <> ".PDF" Then
        fileName = fileName & ".pdf"
    End If
    
    ' دمج المسار الثابت مع اسم الملف للحصول على المسار الكامل
    fullPath = folderPath & fileName
    
    ' التحقق من أن الملف موجود فعلياً داخل المجلد قبل محاولة فتحه
    If Dir(fullPath) = "" Then
        MsgBox "الملف [" & fileName & "] غير موجود في المجلد الثابت:" & vbCrLf & folderPath, vbCritical, "خطأ في المسار"
        Exit Sub
    End If
    
    ' عرض ملف الـ PDF مباشرة داخل أداة الـ WebBrowser في الفورم
    Me.PdfViewer.Navigate fullPath
End Sub











Private Sub ButtonSerach_Click()
Dim tb1 As Boolean, ib As Boolean
Dim R As Long, C As Integer
Dim MyFind, MySrch, MyVelue
''''''''''''''''''''''
Me.ListFind.Clear
If Len(Trim(Me.TextFind)) = 0 Then Exit Sub
'''''''''''''''''''''
C = Me.BoxFind.ListIndex + 1
tb1 = CBool(Me.CheckFindDate.Value = True)
If tb1 Then
    If Not IsDate(Me.TextFind) Then kh_SetDate Me.TextFind
    MyFind = CDbl(CDate(Me.TextFind))
    
Else
    MyFind = Me.TextFind.Value
End If
'''''''''''''''''''''''
With MyRngdate.Cells(2, Ar(C))
    For R = 1 To ContRow
        If Len(Trim(.Cells(R, 1))) Then
            If tb1 Then MySrch = .Cells(R, 1).Value2 Else MySrch = .Cells(R, 1).Value
            ib = InStr(MySrch, MyFind)
            If ib Then
                MyVelue = IIf(IsDate(.Cells(R, 1)), Format(.Cells(R, 1), DtF), .Cells(R, 1))
                Me.ListFind.AddItem MyVelue
                Me.ListFind.List(Me.ListFind.ListCount - 1, 1) = R
            End If
        End If
    Next
End With
'''''''''''''''''''''''''
End Sub

Sub kh_SetAddrss(ByVal MySht As String, ByVal MyAddrs As String, Optional ByVal aSr As String = "")
tSr = TypeName(Evaluate(aSr)) = "Range"
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
With ThisWorkbook
    If tSr Then Set MyRngSeri = .Worksheets(MySht).Range(aSr)
    Set MyRngdate = .Worksheets(MySht).Range(MyAddrs)
End With
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
With MyRngdate
    ContRow = .Worksheet.Cells(Rows.Count, .Column).End(xlUp).Row - .Row
    LastColumn = .Cells.Count
End With
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
Dim Col As Range
Dim ii As Integer
ReDim Ar(1 To LastColumn)
For Each Col In MyRngdate.Cells
    ii = ii + 1
    Ar(ii) = Col.Column - MyRngdate.Column + 1
Next
'''''''''''''''''''''''''''
End Sub

Private Function kh_TestType(rng As Range, Optional iT As Boolean = False) As Boolean
If Not rng.Comment Is Nothing Then
    MyList = Trim(Replace(rng.Comment.Text, Chr(10), ""))
    MyList = Replace(MyList, " ", "")
    If TypeName(Evaluate(MyList)) = "Range" Then
        kh_TestType = True
    End If
End If
End Function



Private Sub BtnPrintPdf_Click()
    Dim fileName As String

    fileName = Trim(Me.TxtPdfPath.Text)

    ' التحقق أولاً من وجود اسم ملف مسجل ومستدعى في الفورم
    If fileName = "" Then
        MsgBox "لا يوجد ملف PDF معروض حالياً لطباعته!", vbExclamation, "تنبيه"
        Exit Sub
    End If

    ' إرسال أمر الطباعة مباشرة لأداة عرض الـ PDF
    ' سيقوم هذا الأمر بفتح نافذة الطباعة الرسمية لتختار منها (كل الصفحات أو الصفحة الحالية)
    On Error Resume Next
    Me.PdfViewer.ExecWB 6, 1 ' الرقم 6 يرمز لأمر الطباعة، والرقم 1 لفتح خيارات الطابعة للمستخدم

    ' في حال حدوث مشكلة توافقية مع بعض إصدارات الويندوز، يتم استخدام طريقة البديل التلقائي
    If Err.Number <> 0 Then
        Err.Clear
        Me.PdfViewer.SetFocus
        SendKeys "^p" ' إرسال اختصار Ctrl + P للوحة المفاتيح لفتح نافذة الطباعة فوراً
    End If
    On Error GoTo 0
End Sub

Private Sub TextSerial1_Change()
Dim ws As Worksheet
    Dim LastRow As Long
    Dim i As Long
    Dim targetSerial As String
    Dim fileName As String
    Dim folderPath As String
    Dim fullPath As String
    Dim found As Boolean

    ' تحديد ورقة العمل بالاسم الصريح لضمان عدم حدوث تداخل
    On Error Resume Next
    Set ws = ThisWorkbook.Sheets("Feuil1")
    On Error GoTo 0

    ' التحقق من وجود الورقة في الملف منعاً لتوجه البرنامج للخطأ
    If ws Is Nothing Then
        MsgBox "خطأ: ورقة العمل 'Feuil1' غير موجودة في هذا الملف!", vbCritical, "تنبيه"
        Exit Sub
    End If

    targetSerial = Trim(Me.TextSerial1.Text)

    ' مسح البيانات وعرض صفحة فارغة في المتصفح إذا كان التكست بكس فارغاً
    If targetSerial = "" Then
        Me.TxtNumber.Text = ""
        Me.TxtName.Text = ""
        Me.TxtPdfPath.Text = ""
        Me.PdfViewer.Navigate "about:blank"
        Exit Sub
    End If

    ' 1. جعل قيمة TxtNumber مطابقة تماماً لقيمة textserial
    Me.TxtNumber.Text = targetSerial

    ' تحديد آخر صف يحتوي على بيانات في العمود A (عمود الأرقام الجديد)
    LastRow = ws.Cells(ws.Rows.Count, "A").End(xlUp).Row
    found = False

    ' 2. البحث عن الرقم في العمود A لجلب البيانات من الصف المقابل بدءاً من الصف 6
    For i = 6 To LastRow
        If Trim(ws.Cells(i, "A").Value) = targetSerial Then
            Me.TxtName.Text = ws.Cells(i, "B").Value    ' جلب الاسم من العمود B الجديد
            Me.TxtPdfPath.Text = ws.Cells(i, "AH").Value ' جلب اسم الملف من العمود AI الجديد
            found = True
            Exit For
        End If
    Next i

    ' إذا لم يتم العثور على الرقم في الجدول
    If Not found Then
        Me.TxtName.Text = "الرقم غير مسجل!"
        Me.TxtPdfPath.Text = ""
        Me.PdfViewer.Navigate "about:blank"
        Exit Sub
    End If

   ' 3. معالجة وعرض ملف الـ PDF مباشرة من المجلد الثابت
    fileName = Trim(Me.TxtPdfPath.Text)
    folderPath = "D:\archac26\"

    ' إذا كان حقل اسم الملف فارغاً في العمود AH
    If fileName = "" Then
        Me.PdfViewer.Navigate "about:blank"
        Exit Sub
    End If

    ' التأكد من وجود امتداد .pdf في نهاية النص
    If UCase(Right(fileName, 4)) <> ".PDF" Then
        fileName = fileName & ".pdf"
    End If

    fullPath = folderPath & fileName

    ' التحقق من وجود الملف في المجلد قبل عرضه
    If Dir(fullPath) <> "" Then
        Me.PdfViewer.Navigate fullPath ' عرض الملف مباشرة داخل الفورم
    Else
        ' إذا لم يجد الملف في المجلد، يعرض صفحة فارغة لكي لا يظهر ملف الموظف السابق
        Me.PdfViewer.Navigate "about:blank"
    End If
End Sub


Private Sub UserForm_Activate()
Dim MyTop As Double, MyWith As Double, MyScrollHeight As Double
Dim MyBox As Control, MyLabl As Control
Dim t As Integer
Dim tTp As Boolean
Dim MyType As String
'''''''''''''''''''''
MyScrollHeight = (LastColumn * iTop) + (Frmtop * 2)
With Frame1
    If MyScrollHeight > .Height Then
        .ScrollBars = 2
        .ScrollHeight = MyScrollHeight
    End If
End With
MyTop = Frmtop: MyWith = Frame1.InsideWidth - (iWgt1 + (Frmlft * 2))
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
For t = 1 To LastColumn
    tTp = kh_TestType(MyRngdate.Cells(1, Ar(t)))
    MyType = IIf(tTp, "Forms.ComboBox.1", "Forms.Textbox.1")
    Set MyBox = Frame1.Controls.Add(MyType, "Textdt" & t, True)
    With MyBox
        .Move Frmlft, MyTop, iWgt1, iHgt
        .TextAlign = 3
        If tTp Then
            .BackColor = 16761024
            .ControlTipText = "إختر من القائمة"
            .List = Range(MyList).Value
        End If
        If MyRngdate.Cells(2, Ar(t)).HasFormula = True Then
            .BackStyle = 0
            .TextAlign = 2
            .SpecialEffect = 3
            .Enabled = False
        End If
    End With
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    Set MyLabl = Frame1.Controls.Add("Forms.Label.1", "Labeldt" & t, True)
    With MyLabl
        .Move iWgt1 + Frmlft, MyTop, MyWith, iHgt
        .SpecialEffect = 3
        .TextAlign = 2
        .Caption = MyRngdate.Cells(1, Ar(t))
    End With
    '''''''''''''''''''''''''''''''''''
    Me.BoxFind.AddItem MyRngdate.Cells(1, Ar(t)).Value2
    MyTop = MyTop + iTop
Next
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
On Error Resume Next
With Me.BoxFind
    .Style = 2
    .Tag = 1
    .ListIndex = 0
End With
On Error GoTo 0
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
With Me.ScrollBar1
    .Max = ContRow
    If ContRow Then
        .Min = 1
    Else
        kh_AddNewRecord
    End If
End With
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
End Sub

Private Sub UserForm_Terminate()
Set MyRngdate = Nothing
Erase Ar
End Sub







' حدث يتفعل تلقائياً فور تغير القيمة داخل textserial
'Private Sub textserial1_Change()
'    Dim ws As Worksheet
'    Dim lastRow As Long
'    Dim i As Long
'   Dim targetSerial As String
'    Dim fileName As String
'    Dim folderPath As String
'    Dim fullPath As String
'    Dim found As Boolean
'    Dim totalPages As Long
'
'    ' تحديد ورقة العمل بالاسم الصريح
'    On Error Resume Next
'    Set ws = ThisWorkbook.Sheets("estmara")
'    On Error GoTo 0
'
'    If ws Is Nothing Then
'        MsgBox "خطأ: ورقة العمل 'estmara' غير موجودة!", vbCritical, "تنبيه"
'        Exit Sub
'    End If
'
'    targetSerial = Trim(Me.TextSerial1.Text)
'
'    ' تفريغ الحقول إذا كان التكست بكس فارغاً
'    If targetSerial = "" Then
'        Me.TxtNumber.Text = ""
'        Me.TxtName.Text = ""
'        Me.TxtPdfPath.Text = ""
'        Me.TxtPageCount.Text = ""
'        Me.PdfViewer.Navigate "about:blank"
'        Exit Sub
'    End If
'
'    Me.TxtNumber.Text = targetSerial
'    lastRow = ws.Cells(ws.Rows.Count, "A").End(xlUp).Row
'    found = False
'
'    ' البحث عن البيانات في الجدول
'    For i = 6 To lastRow
'        If Trim(ws.Cells(i, "A").Value) = targetSerial Then
'            Me.TxtName.Text = ws.Cells(i, "B").Value
'            Me.TxtPdfPath.Text = ws.Cells(i, "AH").Value
'            found = True
'            Exit For
'        End If
'    Next i
'
'    If Not found Then
'        Me.TxtName.Text = "الرقم غير مسجل!"
'        Me.TxtPdfPath.Text = ""
'        Me.TxtPageCount.Text = ""
'        Me.PdfViewer.Navigate "about:blank"
'        Exit Sub
'    End If
'
'    ' معالجة مسار الملف وعرضه
'    fileName = Trim(Me.TxtPdfPath.Text)
'    folderPath = "D:\archacademe26\PDF\"
'
'    If fileName = "" Then
'        Me.TxtPageCount.Text = "لا يوجد ملف"
'        Me.PdfViewer.Navigate "about:blank"
'        Exit Sub
'    End If
'
'    If UCase(Right(fileName, 4)) <> ".PDF" Then
'        fileName = fileName & ".pdf"
'    End If
'
'    fullPath = folderPath & fileName
'
'    ' التحقق من وجود الملف برمجياً
'    If Dir(fullPath) <> "" Then
'        Me.PdfViewer.Navigate fullPath ' عرض الملف
'
'        ' حساب عدد الصفحات وعرضها تلقائياً في التكست بكس الجديد
'        totalPages = GetPDFPageCount(fullPath)
'        If totalPages > 0 Then
'            Me.TxtPageCount.Text = totalPages & " صفحات"
'        Else
'            Me.TxtPageCount.Text = "غير قادر على القراءة"
'        End If
'    Else
'        Me.TxtPageCount.Text = "الملف مفقود"
'        Me.PdfViewer.Navigate "about:blank"
'    End If
'End Sub
'
'' كود زر الطباعة المدمج
'Private Sub BtnPrintPdf_Click()
'    Dim fileName As String
'    fileName = Trim(Me.TxtPdfPath.Text)
'
'    If fileName = "" Then
'        MsgBox "لا يوجد ملف PDF معروض حالياً لطباعته!", vbExclamation, "تنبيه"
'        Exit Sub
'    End If
'
'    On Error Resume Next
'    Me.PdfViewer.ExecWB 6, 1
'
'    If Err.Number <> 0 Then
'        Err.Clear
'        Me.PdfViewer.SetFocus
'        SendKeys "^p"
'    End If
'    On Error GoTo 0
'End Sub



' وظيفة ذكية (Function) لقراءة عدد صفحات ملف الـ PDF من الهيكل الداخلي للملف
'Private Function GetPDFPageCount(ByVal filePath As String) As Long
'
'    Dim hFile As Integer
'   Dim fileContent As String
'   Dim pagePos As Long
'    Dim countPos As Long
'    Dim pageCount As Long
'    Dim countStr As String
'    Dim i As Long
'
'    On Error Resume Next
'    hFile = FreeFile
'    Open filePath For Input As #hFile
'
'    ' قراءة الملف بالكامل كمص نصي للبحث عن وسوم الصفحات الكامنة فيه
'    fileContent = Input(LOF(hFile), hFile)
'    Close #hFile
'    On Error GoTo 0
'
'    pageCount = 0
'    pagePos = 1
'
'    ' الطريقة الأولى: البحث عن وسوم العرض القياسية الكامنة في ملفات PDF (/Count)
'    Do
'        countPos = InStr(pagePos, fileContent, "/Count", vbTextCompare)
'        If countPos = 0 Then Exit Do
'
'        ' استخراج الرقم المصاحب لوسم العدد
'        countStr = ""
'        For i = countPos + 6 To countPos + 15
'            If IsNumeric(Mid(fileContent, i, 1)) Then
'                countStr = countStr & Mid(fileContent, i, 1)
'            Else
'                If countStr <> "" Then Exit For
'            End If
'        Next i
'
'        If Val(countStr) > pageCount Then pageCount = Val(countStr)
'        pagePos = countPos + 6
'    Loop
'
'    ' الطريقة البديلة (الاحتياطية): إذا فشلت الطريقة الأولى، يتم عد وسوم (/Page) الفردية للملف
'    If pageCount = 0 Then
'        pagePos = 1
'        Do
'            pagePos = InStr(pagePos, fileContent, "/Type /Page", vbTextCompare)
'            If pagePos = 0 Then Exit Do
'            pageCount = pageCount + 1
'            pagePos = pagePos + 11
'        Loop
'
'        ' فحص وسم فرعي آخر متداول في ملفات الـ PDF للحماية
'        If pageCount = 0 Then
'            pagePos = 1
'            Do
'                pagePos = InStr(pagePos, fileContent, "/Type/Page", vbTextCompare)
'                If pagePos = 0 Then Exit Do
'                pageCount = pageCount + 1
'                pagePos = pagePos + 10
'            Loop
'        End If
'    End If
'
'    GetPDFPageCount = pageCount
'End Function




