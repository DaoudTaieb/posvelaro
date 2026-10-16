' Lance Golden Pos (Laravel + Vite) sans fenetre et memorise les PID
' des processus lances dans .golden_pos.pid, pour que Fermer_Projet.bat
' n'arrete que ces processus-la (et jamais les autres php.exe / node.exe).

Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")
Set wmi = GetObject("winmgmts:{impersonationLevel=impersonate}!\\.\root\cimv2")

currentDir = fso.GetParentFolderName(WScript.ScriptFullName)
pidFile = currentDir & "\.golden_pos.pid"

' Arrete les serveurs lances precedemment par ce script (evite les conflits de port)
ArreterAnciensServeurs

' Lance les serveurs en arriere-plan et note leurs PID
pids = ""
pidPhp = Lancer("cmd.exe /c php artisan serve --port=8001")
If pidPhp <> "" Then pids = pids & pidPhp & vbCrLf
pidVite = Lancer("cmd.exe /c npm run dev")
If pidVite <> "" Then pids = pids & pidVite & vbCrLf

Set f = fso.CreateTextFile(pidFile, True)
f.Write pids
f.Close

' Pause pour laisser le temps aux serveurs de demarrer, puis ouvre le navigateur
WScript.Sleep 3000
WshShell.Run "http://127.0.0.1:8001"

' Cree un processus cache (fenetre masquee) dans le dossier du projet
' et renvoie son PID ("" en cas d'echec).
Function Lancer(commande)
    Set startup = wmi.Get("Win32_ProcessStartup")
    Set config = startup.SpawnInstance_
    config.ShowWindow = 0

    ' Transmet l'environnement de l'utilisateur (PATH vers php, npm...)
    Set env = WshShell.Environment("Process")
    ReDim variables(env.Count - 1)
    i = 0
    For Each variable In env
        variables(i) = variable
        i = i + 1
    Next
    config.EnvironmentVariables = variables

    Set proc = wmi.Get("Win32_Process")
    retour = proc.Create(commande, currentDir, config, pid)
    If retour <> 0 Then
        MsgBox "Impossible de lancer : " & commande & " (code " & retour & ")", vbExclamation, "Golden Pos"
        Lancer = ""
    Else
        Lancer = CStr(pid)
    End If
End Function

' Lit .golden_pos.pid et arrete les processus listes (avec leurs enfants)
Sub ArreterAnciensServeurs()
    If Not fso.FileExists(pidFile) Then Exit Sub
    Set f = fso.OpenTextFile(pidFile, 1)
    Do Until f.AtEndOfStream
        ligne = Trim(f.ReadLine)
        If ligne <> "" And IsNumeric(ligne) Then
            WshShell.Run "taskkill /F /T /PID " & ligne, 0, True
        End If
    Loop
    f.Close
    fso.DeleteFile pidFile, True
End Sub
