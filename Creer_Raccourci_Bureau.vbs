Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

' Get current directory where this script is located
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)

' Get Desktop path
strDesktop = WshShell.SpecialFolders("Desktop")

' Create the shortcut on the Desktop
Set oShellLink = WshShell.CreateShortcut(strDesktop & "\Lancer Golden Pos.lnk")

oShellLink.TargetPath = "wscript.exe"
oShellLink.Arguments = """" & currentDir & "\lancer_invisible.vbs"""
oShellLink.WindowStyle = 1
oShellLink.IconLocation = currentDir & "\public\logogfm.ico"
oShellLink.Description = "Lancer l'application Golden Pos"
oShellLink.WorkingDirectory = currentDir
oShellLink.Save

' Create another shortcut inside the folder just in case
Set oLocalLink = WshShell.CreateShortcut(currentDir & "\Lancer Golden Pos.lnk")
oLocalLink.TargetPath = "wscript.exe"
oLocalLink.Arguments = """" & currentDir & "\lancer_invisible.vbs"""
oLocalLink.WindowStyle = 1
oLocalLink.IconLocation = currentDir & "\public\logogfm.ico"
oLocalLink.Description = "Lancer l'application Golden Pos"
oLocalLink.WorkingDirectory = currentDir
oLocalLink.Save

WScript.Echo "Raccourcis crees avec succes ! Vous trouverez 'Lancer Golden Pos' sur votre bureau et dans ce dossier."
