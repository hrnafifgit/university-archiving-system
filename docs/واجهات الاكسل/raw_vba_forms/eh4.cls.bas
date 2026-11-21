Attribute VB_Name = "eh4"
Attribute VB_Base = "0{00020820-0000-0000-C000-000000000046}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = True
Private Sub Worksheet_SelectionChange(ByVal Target As Range)
    Dim Rng1 As Range
     Set Rng1 = Range("a1:k89")
     If Intersect(Target, Rng1) Is Nothing Then
  sw = 1
     Else
   Range("c2").Select

      End If
End Sub
Private Sub Worksheet_Activate()
   On Error Resume Next

    ' تحديد نطاق الحركة المسموح به فقط
    Me.ScrollArea = "A1:k88"

    ' تلوين المساحة الخارجية بالرمادي لحجبها بصرياً
    Application.ScreenUpdating = False
    Dim extGrid As Range
      Set extGrid = Me.Range("L1:XFD1048576,A89:L1048576")
   extGrid.Interior.Color = RGB(19, 132, 249) ' لون ازرق سماوي
    ' تشغيل التحديث الديناميكي للصفوف
   ' Call ManageRows
    Application.ScreenUpdating = False
    On Error GoTo 0
End Sub




    'extGrid.Interior.Color = RGB(64, 64, 64) ' لون رمادي غامق
   ' extGrid.Interior.Color = RGB(31, 56, 100) ' لون ازرق
  ' extGrid.Interior.Color = RGB(220, 251, 221) ' لون اخضر جميل فاتح
  '  extGrid.Interior.Color = RGB(190, 246, 250) ' لون سماوي فضي
   ' extGrid.Interior.Color = RGB(252, 212, 52) ' لون ذهبي
  '  extGrid.Interior.Color = RGB(4, 110, 89) ' لون اخضر غامق جميل
   ' extGrid.Interior.Color = RGB(33, 89, 103) ' لون اخضر غامق جميل
 
