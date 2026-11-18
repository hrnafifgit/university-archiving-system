Attribute VB_Name = "UserForm66"
Attribute VB_Base = "0{C43138D7-25AE-482E-B42F-DC8D91F12F8A}{6A5D5E25-B174-4CA0-AEBE-BB1DAE71ED7C}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False
Private Sub CheckBox1_Click()
Kh_TextFind_Change
End Sub

'Private Sub CheckBox2_Click()
'Kh_TextFind_Change
'End Sub

'Private Sub CheckBox3_Click()
'Kh_TextFind_Change
'End Sub

'Private Sub CommandButton1_Click()
'Unload Me
'End Sub

Private Sub CommandButton1_Click()
On Error Resume Next
Unload Me
 Sheets("Feuil1").Activate
'MenuForm.Show
On Error GoTo 0
End Sub
'Private Sub Kh_ListFind_Click()
'On Error Resume Next
'Dim S_1 As String
'S_1 = Kh_ListFind.List(Kh_ListFind.ListIndex, 2)
'Range(S_1).Select
'On Error GoTo 0
'End Sub

Private Sub Kh_ListFind_Click()
Dim S_1 As String, S_2 As String
Dim MySh As Worksheet
On Error Resume Next
S_1 = Kh_ListFind.List(Kh_ListFind.ListIndex, 2)
S_2 = Kh_ListFind.List(Kh_ListFind.ListIndex, 3)
Set MySh = Sheets(S_1)
With MySh
    .Select
    .Range(S_2).Select
End With
On Error GoTo 0
End Sub

Private Sub Kh_TextFind_Change()
On Error Resume Next
Dim MySh As Worksheet
Dim R As Integer, V As Integer, LastRow As Integer
Dim M As String
Dim A, F
Kh_ListFind.Clear
If Kh_TextFind.Text = "" Then GoTo 1
M = Kh_TextFind.Text
For R = 1 To 3
    If Me.Controls("CheckBox" & R).Value = True Then
        Set MySh = Sheets(Me.Controls("CheckBox" & R).Tag)
        With MySh
            LastRow = .Cells(.Rows.Count, "B").End(xlUp).Row
            Set A = .Range("B6:B" & LastRow).Find(M)
            If Not A Is Nothing Then
                F = A.Address
                Do
                If Application.WorksheetFunction.Search(M, A, 1) = 1 Then
                    Kh_ListFind.AddItem A.Value
                    Kh_ListFind.List(V, 1) = .Cells(3, 1).Value
                    Kh_ListFind.List(V, 2) = Me.Controls("CheckBox" & R).Tag
                    Kh_ListFind.List(V, 3) = A.Address
                    V = V + 1
                 End If
                Set A = .Range("B6:B" & LastRow).FindNext(A)
                Loop While Not A Is Nothing And A.Address <> F
            End If
        End With
    End If
Next
On Error GoTo 0
1 End Sub

Private Sub UserForm_Activate()
On Error Resume Next
CheckBox1.Tag = "Feuil1"
'CheckBox2.Tag = "scc2"
'CheckBox3.Tag = "scc3"
CheckBox1.Value = True
On Error GoTo 0
End Sub

Private Sub CommandButton4_Click()
On Error Resume Next
Unload Me
' Sheets("Feuil1").Activate
MenuForm.Show
On Error GoTo 0
End Sub
'
Private Sub UserForm_Initialize()
    On Error Resume Next
  Sheets("Feuil1").Activate
    'Set Worksheet = Sheets("Feuil1")
    On Error GoTo 0
End Sub


