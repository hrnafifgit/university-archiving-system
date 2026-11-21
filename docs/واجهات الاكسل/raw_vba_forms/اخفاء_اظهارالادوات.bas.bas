Attribute VB_Name = "اخفاء_اظهارالادوات"

Sub معاينة_طباعة()
ActiveWorkbook.Application.DisplayFullScreen = False
ActiveWorkbook.Application.DisplayFormulaBar = True
ActiveWindow.SelectedSheets.PrintPreview
End Sub
Sub طباعة_ورقة_واحدة()
    ActiveWindow.SelectedSheets.PrintOut Copies:=1, Collate:=True
End Sub


Sub raka()
bisho.Show
End Sub


