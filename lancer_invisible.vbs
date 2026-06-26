Set fso = CreateObject("Scripting.FileSystemObject")
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)

Set WshShell = CreateObject("WScript.Shell")

' Ferme les anciens serveurs s'ils sont ouverts (pour eviter les bugs de port)
WshShell.Run "cmd.exe /c taskkill /F /IM php.exe /T", 0, True
WshShell.Run "cmd.exe /c taskkill /F /IM node.exe /T", 0, True

' Lance le serveur Laravel en arriere-plan (0 = invisible)
WshShell.Run "cmd.exe /c cd /d """ & currentDir & """ && php artisan serve", 0, False

' Lance le serveur Vite en arriere-plan
WshShell.Run "cmd.exe /c cd /d """ & currentDir & """ && npm run dev", 0, False

' Pause de 3 secondes pour laisser le temps aux serveurs de demarrer
WScript.Sleep 3000

' Ouvre le navigateur web
WshShell.Run "http://127.0.0.1:8000"
