Attribute VB_Name = "Feuil1"
Attribute VB_Base = "0{00020820-0000-0000-C000-000000000046}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = True
'$A$1:$AR$1500

Private Sub Worksheet_Activate()

   ' تحديد نطاق الحركة المسموح به فقط
    Me.ScrollArea = "A1:AR1500"

    ' تلوين المساحة الخارجية بالرمادي لحجبها بصرياً
    Application.ScreenUpdating = False
    Dim extGrid As Range
      Set extGrid = Me.Range("AS1:XFD1048576,A1501:AR1048576")
'    'extGrid.Interior.Color = RGB(64, 64, 64) ' لون رمادي غامق
'   ' extGrid.Interior.Color = RGB(31, 56, 100) ' لون ازرق
'  ' extGrid.Interior.Color = RGB(220, 251, 221) ' لون اخضر جميل فاتح
'  '  extGrid.Interior.Color = RGB(190, 246, 250) ' لون سماوي فضي
'   ' extGrid.Interior.Color = RGB(252, 212, 52) ' لون ذهبي
'  '  extGrid.Interior.Color = RGB(4, 110, 89) ' لون اخضر غامق جميل
'
'
    extGrid.Interior.Color = RGB(33, 89, 103) ' لون اخضر غامق جميل
    'extGrid.Interior.Color = RGB(19, 132, 249) ' لون ازرق سماوي
    ' تشغيل التحديث الديناميكي للصفوف
'    Call ManageRows
'    Application.ScreenUpdating = False
End Sub
'
''Private Sub Worksheet_Change(ByVal Target As Range)
''
''    ' تحديث الصفوف والتنسيق فور إدخال بيانات جديدة في النطاق
''    If Not Intersect(Target, Me.Range("A6:Aq1500")) Is Nothing Then
''        Application.ScreenUpdating = False
''        Call ManageRows
''        Application.ScreenUpdating = True
''    End If
''End Sub
'
''Sub ManageRows()
''   Dim r As Long
''   Dim lastFilledRow As Long
''
''    lastFilledRow = 5 ' العناوين تبدأ من الصف 5
''
''    ' تنسيق العناوين A5:Z5 ثابت
''    With Me.Range("A5:AQ5")
''        .Font.Name = "Arial"
''        .Font.Bold = True
''        .Font.Size = 22
''   '     .HorizontalAlignment = xlCenter
''   '     .EntireColumn.AutoFit
''   '
''   '
''   '
''   '     .WrapText = True
''    End With
''
''    ' البحث عن آخر صف يحتوي على بيانات فعالة
''    For r = 6 To 1500
''        If Application.WorksheetFunction.CountA(Me.Range("A" & r & ":Aq" & r)) > 0 Then
''            lastFilledRow = r
''            ' تطبيق التنسيق والحدود على الصف المكتوب فيه
''            With Me.Range("A" & r & ":Aq" & r)
''                .Font.Name = "Arial"
''                .Font.Bold = True
''                .Font.Size = 16
''                .Borders.LineStyle = xlContinuous
''
''
''
''                .RowHeight = 50
''
''                '.Rows.Height = 30
''             '  .HorizontalAlignment = xlCenter
''              '  .VerticalAlignment = xlCenter
''   '             .EntireColumn.AutoFit
''                ' التكيف التلقائي مع حجم الخلية دون تغيير عرض العمود بشكل مبالغ فيه
''   '             .WrapText = True
''            End With
''        End If
''    Next r
''
''    ' إظهار الصفوف المليئة + صف واحد فارغ تحتها فقط
''    Me.Rows("6:1500").Hidden = False
''    If lastFilledRow < 1500 Then
''
''
''
''   '''''''''''''''''''''''''''''''''''''''''''''''
''        ' تنسيق الصف الفارغ القادم (الحدود والألوان)
''       With Me.Range("A" & (lastFilledRow + 1) & ":AQ" & (lastFilledRow + 1))
''            .Font.Name = "Arial"
'''            .Font.Bold = True
' ''           .Font.Size = 16
'  '          .RowHeight = 50
'  '        '  .Rows.Height = 30
'  '          .Borders.LineStyle = xlContinuous
'  '          .Interior.Color = RGB(245, 245, 245) ' لون تمييز خفيف للصف الفارغ الجاهز للكتابة
'  '      End With
''
''        ' إخفاء بقية الصفوف الفارغة تماماً
''        If lastFilledRow + 2 <= 1500 Then
''            Me.Rows((lastFilledRow + 2) & ":1500").Hidden = True
''        End If
''    End If
''End Sub
''
''
