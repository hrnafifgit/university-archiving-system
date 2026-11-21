Attribute VB_Name = "star2"
Attribute VB_Base = "0{00020820-0000-0000-C000-000000000046}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = True
Private Sub Worksheet_SelectionChange(ByVal Target As Range)

    Dim Rng1 As Range
     Set Rng1 = Range("a1:S36")
     If Intersect(Target, Rng1) Is Nothing Then
  sw = 1
     Else
   Range("c2").Select

      End If
End Sub


Private Sub Worksheet_Change(ByVal Target As Range)
    ' تحديث الصفوف والتنسيق فور إدخال بيانات جديدة في النطاق
    If Not Intersect(Target, Me.Range("A1:S36")) Is Nothing Then
      '  Application.ScreenUpdating = False
        Application.ScreenUpdating = True
    End If
End Sub








'' $A$1:$R$37
'Private Sub CommandButton13_Click()
' Application.VBE.MainWindow.Visible = True
'End Sub
'
'Private Sub CommandButton15_Click()
'saiedbiram.Show
'End Sub
'Private Sub CommandButton14_Click()
'  On Error Resume Next
'        With Application
'           .DisplayFullScreen = False
'           ActiveWorkbook.Application.DisplayFormulaBar = True
'           .CommandBars("MyToolbar").Enabled = False
'           .CommandBars("Worksheet Menu Bar").Enabled = False
'            .CommandBars("standerbar").Enabled = False
'        End With
'        MyTest = False
'    On Error GoTo 0
'End Sub
'Private Sub CommandButton12_Click()
'  On Error Resume Next
'        With Application
'           .DisplayFullScreen = True
'           .CommandBars("MyToolbar").Enabled = True
'           .CommandBars("Worksheet Menu Bar").Enabled = True
'           .CommandBars("standerbar").Enabled = True
'        End With
'        MyTest = False
'    On Error GoTo 0
'    End Sub
'Private Sub CommandButton7_Click()
'UserForm11.Show
'End Sub
'Private Sub CommandButton9_Click()
'Application.ScreenUpdating = False
'Application.Quit
'End Sub
'
'
'
