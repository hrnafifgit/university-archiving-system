Attribute VB_Name = "ورقة16"
Attribute VB_Base = "0{00020820-0000-0000-C000-000000000046}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = True
Private Sub Worksheet_SelectionChange(ByVal Target As Range)

    Dim Rng1 As Range
     Set Rng1 = Range("a1:W44")
     If Intersect(Target, Rng1) Is Nothing Then
  sw = 1
     Else
   Range("V7").Select

      End If
End Sub





Private Sub Worksheet_Activate()
  
  On Error Resume Next
     ' تحديد نطاق الحركة المسموح به فقط
    'Me.ScrollArea = "b3:R30"
    Me.ScrollArea = "A1:W43"
    ' تلوين المساحة الخارجية بالرمادي لحجبها بصرياً
    Application.ScreenUpdating = False
    Dim extGrid As Range
    Set extGrid = Me.Range("X1:XFD1048576,A44:Z1048576")
   ' Set extGrid = Me.Range("A1:A50 , a1:XFD2, X1:XFD1048576,A44:Z1048576")
    'extGrid.Interior.Color = RGB(64, 64, 64) ' لون رمادي غامق
    'extGrid.Interior.Color = RGB(252, 212, 52) ' لون ذهبي
   ' extGrid.Interior.Color = RGB(4, 110, 89) ' لون اخضر غامق جميل
   ' extGrid.Interior.Color = RGB(19, 132, 249) ' لون ازرق سماوي
    ' تشغيل التحديث الديناميكي للصفوف
   ' Call ManageRows
    Application.ScreenUpdating = False
    
     On Error GoTo 0
End Sub


Private Sub Worksheet_Change(ByVal Target As Range)
    ' تحديث الصفوف والتنسيق فور إدخال بيانات جديدة في النطاق
    If Not Intersect(Target, Me.Range("A1:W43")) Is Nothing Then
        Application.ScreenUpdating = False
        Application.ScreenUpdating = True
    End If
End Sub

