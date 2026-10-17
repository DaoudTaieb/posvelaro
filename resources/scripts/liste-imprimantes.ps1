<#
    Golden Pos - liste des imprimantes installées sur ce poste, au format JSON.
    Utilisé par Paramètres > Imprimantes et douchettes pour proposer le nom exact de l'imprimante.

    Usage : powershell -NoProfile -ExecutionPolicy Bypass -File liste-imprimantes.ps1
#>
$ErrorActionPreference = 'Stop'
# Sorties lues par Golden Pos (PHP) : UTF-8, pour des messages avec accents lisibles
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Win32_Printer : disponible sur toutes les versions de Windows (Get-Printer n'existe pas partout)
$etats = @{ 1 = 'autre'; 2 = 'inconnu'; 3 = 'pret'; 4 = 'impression'; 5 = 'prechauffage'; 6 = 'arrete'; 7 = 'hors_ligne' }
try {
    $liste = @(Get-CimInstance -ClassName Win32_Printer | ForEach-Object {
        [pscustomobject]@{
            nom        = $_.Name
            pilote     = $_.DriverName
            port       = $_.PortName
            etat       = if ($_.WorkOffline) { 'hors_ligne' } elseif ($etats.ContainsKey([int]$_.PrinterStatus)) { $etats[[int]$_.PrinterStatus] } else { 'inconnu' }
            defaut     = [bool]$_.Default
            reseau     = [bool]$_.Network
        }
    })
    [Console]::Out.Write((ConvertTo-Json -InputObject $liste -Compress -Depth 2))
    exit 0
} catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
}
