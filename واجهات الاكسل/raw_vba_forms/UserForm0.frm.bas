Attribute VB_Name = "UserForm0"
Attribute VB_Base = "0{D222628D-503F-472C-8966-2604CA8690B5}{99FA8067-1337-4AFF-ACEF-C5FB9B1B3D7F}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False


Private Sub CommandButton1_Click()
  TextBox1.SetFocus
If TextBox1.Value = 123 Then
Unload Me

MenuForm.Show
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

Private Sub UserForm_Activate()
TextBox1.SetFocus
End Sub


