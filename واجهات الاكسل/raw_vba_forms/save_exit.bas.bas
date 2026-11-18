Attribute VB_Name = "save_exit"
Sub save()
 ActiveWorkbook.save
Call MsgBox("  تم الحفظ بنجاح  ", mBox, "عبدالبارى البنا")
End Sub
 Sub exit_all()
Application.Quit
 End Sub



' اخفاء الادوات
Sub hidbisher()
  On Error Resume Next
        With Application
           .DisplayFullScreen = True
           ActiveWorkbook.Application.DisplayFormulaBar = True
                    .CommandBars("MyToolbar").Enabled = True
           .CommandBars("Worksheet Menu Bar").Enabled = True
           .CommandBars("standerbar").Enabled = True
           ActiveWindow.DisplayHeadings = False
           Application.WindowState = xlMaximized
        End With
    On Error GoTo 0
End Sub
'اظهار الادوات
Sub shobisher()
  On Error Resume Next
        With Application
           .DisplayFullScreen = False
                ActiveWindow.DisplayHeadings = True
              ActiveWorkbook.Application.DisplayFormulaBar = True
           .CommandBars("MyToolbar").Enabled = False
           .CommandBars("Worksheet Menu Bar").Enabled = False
            .CommandBars("standerbar").Enabled = False
            Application.WindowState = xlMaximized
        End With
    On Error GoTo 0
End Sub

