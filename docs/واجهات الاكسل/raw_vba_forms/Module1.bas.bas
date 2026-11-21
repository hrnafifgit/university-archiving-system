Attribute VB_Name = "Module1"
Option Explicit
'======================================================
'======================================================
'  kh_SetAddrss متغيرات
' ( اولا : اسم ورقة البيانات (افتراضي
' ( ثانيا : نطاق صف رؤوس اعمدة البيانات (افتراضي
'''''''''''''''''''''''''''''''''''''''''''''''''
' ( ثالثا : عمود التسلسل  (اختياري
'   اذا اردت ادخال رقم تسلسل البيانات الخاص بالفورم
'   تلقائيا في عمود معين سجل عنوان راس  العمود
'======================================================
'======================================================

Sub kh_Show_UFormChang()
'On Error GoTo 1
With UFormChang
    .kh_SetAddrss "Feuil1", "A5:AR5"
   ' UFormChang.Show
   
End With
UFormChang.Show

'1:
'If Err Then MsgBox "تاكد من صحة ادخال المتغيرات الاساسية في  : " & vbCr & vbCr & "kh_SetAddrss", vbCritical + vbMsgBoxRight + vbMsgBoxRtlReading, "استخدام خاطىء"
'On Error GoTo 0
End Sub




Sub kh_Show_UFormChang3()
'On Error GoTo 1
With UFormChang9
    .kh_SetAddrss "Feuil1", "A5:AR5"
    
   ' UFormChang.Show
End With
UFormChang9.Show

'1:
'If Err Then MsgBox "تاكد من صحة ادخال المتغيرات الاساسية في  : " & vbCr & vbCr & "kh_SetAddrss", vbCritical + vbMsgBoxRight + vbMsgBoxRtlReading, "استخدام خاطىء"
'On Error GoTo 0
End Sub



Sub Click()

UserForm66.Show
End Sub


Sub GrabData()
    Dim lr As Long, i As Long
    Dim ws As Worksheet, SH As Worksheet
    Set ws = Sheets("Sheet1"): Set SH = Sheets("Sheet2")
    lr = ws.Cells(Rows.Count, 3).End(xlUp).Row
    Application.ScreenUpdating = False
        SH.Range("C10:Q1000").ClearContents
        For i = 10 To lr
            If ws.Cells(i, 7) = SH.Range("L6") And ws.Cells(i, 8) = SH.Range("L7") And ws.Cells(i, 10) = SH.Range("L8") Then
                ws.Cells(i, 3).Resize(3, 5).Copy
                SH.Range("C" & SH.Cells(Rows.Count, 3).End(xlUp).Row + 1).PasteSpecial xlPasteValues
            End If
        Next i
    Application.CutCopyMode = False
    SH.Range("A1").Select
    Application.ScreenUpdating = True
End Sub
Sub user()
MH.Show
End Sub




' كود زر طباعة تقرير باختيار الاعمدة التي تريد
Sub KH_Report_Show()


With Sheets("repor")
    .Select
    .Cells.Delete
End With
kh_form_Report.Show
End Sub

