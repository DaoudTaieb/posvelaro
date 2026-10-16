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

Add-Type -TypeDefinition @"
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

try {
    [GoldenPosImpressionBrute]::Envoyer($Imprimante, [System.IO.File]::ReadAllBytes($Fichier))
    exit 0
} catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
}
