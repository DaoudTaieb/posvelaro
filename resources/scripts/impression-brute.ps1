<#
    Golden Pos - envoi d'un fichier ESC/POS brut (RAW) à une imprimante Windows, par son nom.
    Utilise directement le spouleur (winspool.drv) : aucun partage d'imprimante n'est nécessaire.

    Usage : powershell -NoProfile -ExecutionPolicy Bypass -File impression-brute.ps1 -Imprimante "EPSON TM-T20II Receipt" -Fichier C:\...\ticket.bin
    Code retour : 0 si le travail a été remis au spouleur, 1 sinon (message sur la sortie d'erreur).
#>
param(
    [Parameter(Mandatory = $true)][string]$Imprimante,
    [Parameter(Mandatory = $true)][string]$Fichier
)

$ErrorActionPreference = 'Stop'
# Sorties lues par Golden Pos (PHP) : UTF-8, pour des messages avec accents lisibles
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$source = @"
using System;
using System.Runtime.InteropServices;

public static class GoldenPosImpressionBrute
{
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public class DOCINFO
    {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }

    [DllImport("winspool.drv", EntryPoint = "OpenPrinterW", SetLastError = true, CharSet = CharSet.Unicode)]
    static extern bool OpenPrinter(string name, out IntPtr handle, IntPtr defaults);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool ClosePrinter(IntPtr handle);
    [DllImport("winspool.drv", EntryPoint = "StartDocPrinterW", SetLastError = true, CharSet = CharSet.Unicode)]
    static extern int StartDocPrinter(IntPtr handle, int level, [In] DOCINFO info);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool EndDocPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool StartPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool EndPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool WritePrinter(IntPtr handle, IntPtr data, int count, out int written);

    public static void Envoyer(string imprimante, byte[] donnees)
    {
        IntPtr h;
        if (!OpenPrinter(imprimante, out h, IntPtr.Zero))
            throw new Exception("Imprimante introuvable : " + imprimante + " (code " + Marshal.GetLastWin32Error() + ")");
        IntPtr buffer = Marshal.AllocCoTaskMem(donnees.Length);
        try
        {
            DOCINFO info = new DOCINFO { pDocName = "Golden Pos - ticket", pDataType = "RAW" };
            if (StartDocPrinter(h, 1, info) == 0)
                throw new Exception("Impossible de démarrer l'impression (code " + Marshal.GetLastWin32Error() + ")");
            StartPagePrinter(h);
            Marshal.Copy(donnees, 0, buffer, donnees.Length);
            int ecrits;
            bool ok = WritePrinter(h, buffer, donnees.Length, out ecrits);
            EndPagePrinter(h);
            EndDocPrinter(h);
            if (!ok || ecrits != donnees.Length)
                throw new Exception("Envoi incomplet à l'imprimante (" + ecrits + "/" + donnees.Length + " octets)");
        }
        finally
        {
            Marshal.FreeCoTaskMem(buffer);
            ClosePrinter(h);
        }
    }
}
"@

# Le pilote d'envoi est compilé une seule fois (environ 250 ms) puis réutilisé depuis le dossier
# temporaire : chaque ticket ne paie plus que le démarrage de PowerShell.
# Changer le numéro de version du fichier si le code C# ci-dessus est modifié.
function Initialiser-PiloteImpression([string]$code) {
    $dossier = Join-Path ([System.IO.Path]::GetTempPath()) 'GoldenPos'
    $dll = Join-Path $dossier 'GoldenPosImpressionBrute-v1.dll'
    try {
        if (-not (Test-Path -LiteralPath $dll)) {
            New-Item -ItemType Directory -Force -Path $dossier | Out-Null
            $temporaire = Join-Path $dossier ('compilation-' + [guid]::NewGuid().ToString('N') + '.dll')
            Add-Type -TypeDefinition $code -OutputAssembly $temporaire -OutputType Library
            try { Move-Item -LiteralPath $temporaire -Destination $dll -ErrorAction Stop }
            catch { Remove-Item -LiteralPath $temporaire -ErrorAction SilentlyContinue }
        }
        Add-Type -LiteralPath $dll
    } catch {
        # Dossier temporaire inaccessible ou fichier verrouillé : compilation en mémoire, comme avant
        if (-not ('GoldenPosImpressionBrute' -as [type])) { Add-Type -TypeDefinition $code }
    }
}

try {
    Initialiser-PiloteImpression $source
    [GoldenPosImpressionBrute]::Envoyer($Imprimante, [System.IO.File]::ReadAllBytes($Fichier))
    exit 0
} catch {
    # Message utile seulement (sans l'enveloppe « Exception lors de l'appel de Envoyer… » de PowerShell)
    $erreur = $_.Exception
    while ($erreur.InnerException) { $erreur = $erreur.InnerException }
    [Console]::Error.WriteLine($erreur.Message)
    exit 1
}
