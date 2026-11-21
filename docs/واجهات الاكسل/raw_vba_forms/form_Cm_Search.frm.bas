Attribute VB_Name = "form_Cm_Search"
Attribute VB_Base = "0{A35C27D7-3B9D-46E8-B400-B756EF0E3B3B}{78425C0B-C5DA-4CE6-92F8-26B858B2A525}"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Attribute VB_TemplateDerived = False
Attribute VB_Customizable = False
Private Sub Button1_Click()
On Error Resume Next
With CM_ListFind
     prnn.Range("AA1").Value = .List(.ListIndex, 0)
     prnn.Range("AB1").Value = .List(.ListIndex, 1)
End With
Unload Me
    Application.Visible = True
    Sheets("Prnn").Activate = True
    Sheets("Prnn").Select = True
'    Call hidshobisher
    On Error GoTo 0
'Call زيادةأونقصان8_تغيير
End Sub
Private Sub Button2_Click()
On Error Resume Next
Unload Me
'    Application.Visible = True
'    Sheets("Prnn").Activate = True
'    Sheets("Prnn").Select = True
'Call shobisher
    
    
    On Error GoTo 0
End Sub
Private Sub CM_ListFind_Change()
With CM_ListFind
    If .ListIndex <> -1 Then Button1.Enabled = True Else Button1.Enabled = False
End With
End Sub
Private Sub CM_TextFind_Change()
Dim LastRow As Integer, R As Integer, t As Integer
On Error Resume Next
CM_ListFind.Clear
With Feuil1
    LastRow = .Cells(.Rows.Count, 2).End(xlUp).Row
    For R = 6 To LastRow
        If .Cells(R, 2) Like "*" & CM_TextFind.Text & "*" Then
            CM_ListFind.AddItem
            CM_ListFind.List(t, 0) = .Cells(R, 1)
            CM_ListFind.List(t, 1) = .Cells(R, 2)
            t = t + 1
        End If
    Next
End With
On Error GoTo 0
End Sub




Private Sub CommandButton1_Click()
On Error Resume Next
   Application.Visible = True
    Unload Me
    Sheets("prnn").Activate
    'Sheets("sheet2").Select
       On Error GoTo 0
End Sub

Private Sub UserForm_Activate()
On Error Resume Next
CM_TextFind_Change
 Sheets("prnn").Activate
       On Error GoTo 0
 
End Sub
'  Me.ComboBox1.Value
