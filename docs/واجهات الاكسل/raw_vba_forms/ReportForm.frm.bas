Attribute VB_Name = "ReportForm"
Attribute VB_Base = "0{113EA928-5333-4A15-B628-8692C4DB64A2}{49E615D6-63EF-4AE9-97D1-CA44C29D69F1}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False


Private Sub Button2_Click()
On Error Resume Next
Unload Me
'    Application.Visible = True
'    Sheets("Prnn").Activate = True
'    Sheets("Prnn").Select = True
'Call shobisher
MenuForm.Show
      On Error GoTo 0
End Sub



Private Sub UserForm_Initialize()
    Dim wsSetting As Worksheet, wsFeuil1 As Worksheet
    Dim cell As Range, colCell As Range
    On Error Resume Next
    ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    Set wsSetting = Sheets("seting1")
    Set wsFeuil1 = Sheets("feuil1")
   cmbEntities.Clear
    cmbEntities.AddItem "الكل"
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    For Each cell In wsSetting.Range("D4:D21")
        If cell.Value <> "" Then cmbEntities.AddItem cell.Value
    Next cell
    cmbEntities.ListIndex = -1 ' جعل الكومبوبكس فارغاً عند البدء
    
   ' 2. تفعيل خيار التحديد المتعدد وضبط عرض العمود لإظهار الأسماء كاملة
    lstColumns.MultiSelect = fmMultiSelectMulti
    lstColumns.ListStyle = fmListStyleOption
    lstColumns.ColumnWidths = "150" ' تكبير عرض العمود داخل القائمة لمنع اختفاء الأسماء
    
    ' 3. تعبئة القائمة بعناوين الأعمدة من صفحة feuil2 وتصفير الخيارات
    lstColumns.Clear
    For Each colCell In wsFeuil1.Range("A5:AR5")
        If colCell.Value <> "" Then
            lstColumns.AddItem colCell.Value
        Else
            lstColumns.AddItem "عمود بدون عنوان (" & Split(colCell.Address, "$")(1) & ")"
        End If
    Next colCell
      On Error GoTo 0
End Sub
 

Private Sub btnTransfer_Click()
    Dim wsFeuil1 As Worksheet, wsRepj1 As Worksheet
    Dim lastRowF2 As Long, nextRowRep As Long, R As Long, C As Long, i As Long
    Dim isChecked As Boolean, matchEntity As Boolean
    Dim colCount As Long, repCol As Long
    Dim selectedCols() As Long
    Dim reportTitle As String
    On Error Resume Next
     
    ' التحققات الأولية
    If cmbEntities.ListIndex = -1 Then
        MsgBox "الرجاء اختيار الجهة أولاً قبل الترحيل!", vbCritical, "تنبيه"
        Exit Sub
    End If
    
    isChecked = False
    For i = 0 To lstColumns.ListCount - 1
        If lstColumns.Selected(i) Then
            isChecked = True
            Exit For
        End If
    Next i
    
    If Not isChecked Then
        MsgBox "الرجاء اختيار عمود واحد على الأقل للترحيل!", vbCritical, "تنبيه"
        Exit Sub
    End If
    
    Set wsFeuil1 = Sheets("feuil1")
    Set wsRepj1 = Sheets("Repj1")
    
    ' تسريع العملية لأقصى درجة وتعطيل المحرك المؤقت للإكسل
    With Application
        .ScreenUpdating = False
        .Calculation = xlCalculationManual
        .EnableEvents = False
    End With
    
    ' 1. إلغاء أي حماية أو إخفاء سابق لضمان المعالجة التامة
    wsRepj1.ScrollArea = ""
    wsRepj1.Cells.EntireRow.Hidden = False
    wsRepj1.Cells.EntireColumn.Hidden = False
    wsRepj1.Range("A1:AR1500").Clear
    wsRepj1.Range("A1:AR1500").ClearFormats
    
    ' 2. تخزين أرقام الأعمدة المختارة
    colCount = 0
    For i = 0 To lstColumns.ListCount - 1
        If lstColumns.Selected(i) Then
            colCount = colCount + 1
            ReDim Preserve selectedCols(1 To colCount)
            selectedCols(colCount) = i + 1
        End If
    Next i
    
    ' 3. كتابة وتنسيق عنوان التقرير الديناميكي
    reportTitle = "تقرير باسماء الكادر الاكاديمي في " & cmbEntities.Value
    Dim lastColLetter As String
    lastColLetter = Split(wsRepj1.Cells(1, colCount + 1).Address, "$")(1)
    
    With wsRepj1.Range("A2:" & lastColLetter & "2")
        .Merge
        .Value = reportTitle
        .Font.Name = "Arial"
        .Font.Size = 24
        .Font.Bold = True
        .HorizontalAlignment = xlCenter
        .VerticalAlignment = xlCenter
    End With
    wsRepj1.Rows(2).RowHeight = 50
    
    ' 4. كتابة ترويسة الترقيم التلقائي
    wsRepj1.Range("A5").Value = "م"
    wsRepj1.Range("A5").Font.Bold = True
    wsRepj1.Range("A5").HorizontalAlignment = xlCenter
    wsRepj1.Range("A5").Borders.LineStyle = xlContinuous
    
    ' 5. ترحيل عناوين الأعمدة المختارة إلى الصف 5
    repCol = 2
    For i = 1 To colCount
        wsFeuil1.Cells(5, selectedCols(i)).Copy
        wsRepj1.Cells(5, repCol).PasteSpecial xlPasteAll
        repCol = repCol + 1
    Next i
    wsRepj1.Rows(5).RowHeight = 25
    
    ' 6. ترحيل البيانات السريع بناءً على الفلترة
    lastRowF2 = wsFeuil1.Cells(wsFeuil1.Rows.Count, "P").End(xlUp).Row
    If lastRowF2 < 6 Then lastRowF2 = 6
    
    nextRowRep = 6
    Dim serialNum As Long
    serialNum = 1
    
    For R = 6 To lastRowF2
        matchEntity = False
        If cmbEntities.Value = "الكل" Then
            matchEntity = True
        ElseIf wsFeuil1.Cells(R, "P").Value = cmbEntities.Value Then
            matchEntity = True
        End If
        
        If matchEntity Then
            ' كتابة الترقيم
            wsRepj1.Cells(nextRowRep, "A").Value = serialNum
            wsRepj1.Cells(nextRowRep, "A").HorizontalAlignment = xlCenter
            wsRepj1.Cells(nextRowRep, "A").Borders.LineStyle = xlContinuous
            
            ' نسخ الأعمدة المختارة
            repCol = 2
            For i = 1 To colCount
                wsFeuil1.Cells(R, selectedCols(i)).Copy
                wsRepj1.Cells(nextRowRep, repCol).PasteSpecial xlPasteAll
                repCol = repCol + 1
            Next i
            
            serialNum = serialNum + 1
            nextRowRep = nextRowRep + 1
        End If
    Next R
     
    
    ' 7. حساب الأبعاد الفعلية الدقيقة للبيانات المُرحّلة
    Dim lastUsedRow As Long, lastUsedCol As Long
    lastUsedRow = wsRepj1.Cells(wsRepj1.Rows.Count, "A").End(xlUp).Row
    lastUsedCol = colCount + 1
    
    Dim maxColLetter As String
    maxColLetter = Split(wsRepj1.Cells(1, lastUsedCol).Address, "$")(1)
    
    ' 8. إعدادات الطباعة، التوجيه العرضي، الهوامش الضيقة، وإضافة أرقام الصفحات
    With wsRepj1.PageSetup
       On Error Resume Next
        
    
    
        .PrintArea = "A1:S1" & maxColLetter & lastUsedRow ' منطقة الطباعة الفعالة فقط
        .PrintTitleRows = "$1:$5" ' تكرار العناوين والترويسية
        .Orientation = xlLandscape ' عرض الصفحة
        
        ' تطبيق الهوامش الضيقة
        .LeftMargin = Application.InchesToPoints(0.25)
        .RightMargin = Application.InchesToPoints(0.25)
        .TopMargin = Application.InchesToPoints(0.5)
        .BottomMargin = Application.InchesToPoints(0.5)
        .HeaderMargin = Application.InchesToPoints(0.2)
        .FooterMargin = Application.InchesToPoints(0.2)
        
        ' إضافة أرقام الصفحات
        .CenterFooter = "صفحة &P من &N"
        
        ' تصحيح التوزيع التلقائي (منع تقييد الطول)
        .Zoom = False
        .FitToPagesWide = 1
        .FitToPagesTall = False ' تم تعديلها من 0 إلى False لإصلاح مشكلة الطول
    
  On Error GoTo 0
  
    End With
    
    ' إعادة تشغيل إعدادات الإكسل الأساسية ليتمكن النظام من إظهار المعاينة
    With Application
        .CutCopyMode = False
        .Calculation = xlCalculationAutomatic
        .EnableEvents = True
        .ScreenUpdating = True
    End With
    
    ' 9. إغلاق الفورم واستدعاء معاينة الطباعة وهي تحتوي البيانات بالكامل
   ' Unload Me
  '  wsRepj1.PrintOut Preview:=True
     
    ' 10. تشغيل الحماية وإخفاء الفراغات بعد انتهاء المعاينة والطباعة مباشرة
    Application.ScreenUpdating = False
    
    ' إخفاء الصفوف الفارغة المتبقية بعد الجدول
    If lastUsedRow < 1500 Then
        wsRepj1.Rows(lastUsedRow + 1 & ":1500").Hidden = True
    End If
    
    ' إخفاء الأعمدة الزائدة
    If lastUsedCol < 37 Then
        Dim nextColLetter As String
        nextColLetter = Split(wsRepj1.Cells(1, lastUsedCol + 1).Address, "$")(1)
        wsRepj1.Range(nextColLetter & ":AQ").EntireColumn.Hidden = True
    End If
    
    ' تفعيل حماية حركة مؤشر الماوس بحدود جدول البيانات المُرَحّل فقط
    wsRepj1.ScrollArea = "A1:" & maxColLetter & (lastUsedRow + 1)
    
    Application.ScreenUpdating = True
    MsgBox "تم إنهاء ترحيل وتنسيق التقرير بنجاح تام!", vbInformation, "نجاح"
   
   On Error Resume Next
   Application.Visible = True
    Unload Me
    Sheets("Repj1").Activate
  '  Sheets("Repj1").Select
       On Error GoTo 0
    
End Sub

   Private Sub UserForm_QueryClose(Cancel As Integer, CloseMode As Integer)
 On Error Resume Next
    Application.CutCopyMode = False
       On Error GoTo 0

End Sub


