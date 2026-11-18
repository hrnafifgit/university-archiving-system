Attribute VB_Name = "Module4"



Sub OpenReportForm()
  ReportForm.Show
End Sub

    
    
    
  Sub clear1()
    On Error Resume Next
    Set wsMain = Sheets("Feuil1")
   
   ' تنشيط الشيت الرئيسي وتفعيل التصفية التلقائية إن لم تكن مفعلة
    wsMain.Activate
    wsMain.Range("A5:AR5").AutoFilter
    wsMain.AutoFilterMode = False
    wsMain.ShowAllData = True
       On Error GoTo 0
    End Sub
    
