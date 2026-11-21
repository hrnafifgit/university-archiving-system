Attribute VB_Name = "Module3"
Sub طباعة_البطاقات()
    
Dim i As Integer
    
Sheets("prin1").Select
    
    For i = Range("Y1") To Range("Z1")
        Range("AA1") = i
    If i <= Range("Z1") Then
       ActiveWindow.SelectedSheets.PrintOut Copies:=1, Collate:=True
    
    End If
    
    Next i
    
   End Sub















Sub SetupSheet2_Page()
    ' ????? ?????? ????? ????? ??????? ?? ???????? ????????
    With Sheets("Sheet2").PageSetup
        .Orientation = xlLandscape
        .PrintArea = "$A$1:$S$" & Sheets("Sheet2").Cells(Rows.Count, "A").End(xlUp).Row
        .FitToPagesWide = 1
        .FitToPagesTall = False
    End With
End Sub

Sub Preview_Sheet2()
    ' ?? ?????? ??? ???????
    Call SetupSheet2_Page
    Sheets("Sheet2").PrintOut Preview:=True
End Sub

Sub Print_Sheet2()
    ' ?? ??????? ????????
    Call SetupSheet2_Page
    Sheets("Sheet2").PrintOut
End Sub

Sub Export_Sheet2_PDF()
    ' ?? ??????? ??? PDF ???? ???????
    Call SetupSheet2_Page
    Dim filePath As Variant
    
    filePath = Application.GetSaveAsFilename(InitialFileName:="?????_?????", FileFilter:="PDF Files (*.pdf), *.pdf")
    
    If filePath <> False Then
        Sheets("Sheet2").ExportAsFixedFormat Type:=xlTypePDF, fileName:=filePath, Quality:=xlQualityStandard, IncludeDocProperties:=True, IgnorePrintAreas:=False, OpenAfterPublish:=True
    End If
End Sub




Sub Open_MainMenu()
    ' التأكد من الانتقال لشاشة star1 أولاً ثم فتح الفورم
 '   Sheets("star").Activate
On Error Resume Next
Application.Visible = False
  MenuForm.Show
End Sub



Sub bisher66()
form_Cm_Search.Show
End Sub












