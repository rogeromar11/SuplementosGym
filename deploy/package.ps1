# =============================================================================
# Empaqueta SuplementosGym para subir a public_html (nginx/cPanel).
#
# Uso (desde la raiz o desde deploy/):
#   powershell -ExecutionPolicy Bypass -File deploy\package.ps1
#   powershell -ExecutionPolicy Bypass -File deploy\package.ps1 -NoVendor
#   powershell -ExecutionPolicy Bypass -File deploy\package.ps1 -NoZip
#
# Genera:
#   build\upload\                  -> contenido a subir (espeja public_html)
#   build\suplementosgym-upload.zip -> mismo contenido comprimido para File Manager
#
# No incluye: .git, openscpect/, database/, deploy/, build/, *.md, ni los
# archivos de prueba de uploads/ (solo crea las carpetas con sus guardas).
# =============================================================================
param(
    [switch]$NoVendor,
    [switch]$NoZip
)

$ErrorActionPreference = 'Stop'
$repo  = Split-Path -Parent $PSScriptRoot
$build = Join-Path $repo 'build'
$dest  = Join-Path $build 'upload'
$zip   = Join-Path $build 'suplementosgym-upload.zip'

if (Test-Path -LiteralPath $build) { Remove-Item -LiteralPath $build -Recurse -Force }
New-Item -ItemType Directory -Path $dest -Force | Out-Null

# 1) Archivos raiz que si van
$rootFiles = @('index.php', '.htaccess', 'robots.txt', 'composer.json', 'composer.lock')
foreach ($f in $rootFiles) {
    $src = Join-Path $repo $f
    if (Test-Path -LiteralPath $src) { Copy-Item -LiteralPath $src -Destination $dest -Force }
}

# 2) Carpetas completas
$dirs = @('application', 'system', 'assets')
if (-not $NoVendor) { $dirs += 'vendor' }
foreach ($d in $dirs) {
    $src = Join-Path $repo $d
    if (-not (Test-Path -LiteralPath $src)) { Write-Warning "No existe: $d"; continue }
    robocopy $src (Join-Path $dest $d) /E /NFL /NDL /NJH /NJS /NP /XD '.git' /XF '*.pyc' | Out-Null
}

# 3) uploads: solo estructura + guardas (sin evidencia/comprobantes de prueba)
$uploadDirs = @('delivery_evidence', 'payment_receipts', 'deposit_receipts', 'products')
$guards = @('.htaccess', '.gitignore', 'index.html')
foreach ($u in $uploadDirs) {
    $d = Join-Path $dest "uploads\$u"
    New-Item -ItemType Directory -Path $d -Force | Out-Null
    foreach ($g in $guards) {
        $src = Join-Path $repo "uploads\$u\$g"
        if (Test-Path -LiteralPath $src) { Copy-Item -LiteralPath $src -Destination $d -Force }
    }
}

# 4) Zip (con separador '/' para que extraiga bien en Linux/cPanel).
#    Compress-Archive de PowerShell 5.1 escribe '\' y rompe la extraccion.
if (-not $NoZip) {
    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    if (Test-Path -LiteralPath $zip) { Remove-Item -LiteralPath $zip -Force }
    $base = (Resolve-Path -LiteralPath $dest).Path.TrimEnd('\', '/')
    $archive = [System.IO.Compression.ZipFile]::Open($zip, [System.IO.Compression.ZipArchiveMode]::Create)
    try {
        Get-ChildItem -Recurse -File -Force -LiteralPath $dest | ForEach-Object {
            $rel = $_.FullName.Substring($base.Length + 1).Replace('\', '/')
            $entry = $archive.CreateEntry($rel, [System.IO.Compression.CompressionLevel]::Optimal)
            $out = $entry.Open()
            $in = [System.IO.File]::OpenRead($_.FullName)
            try { $in.CopyTo($out) } finally { $in.Dispose(); $out.Dispose() }
        }
    } finally { $archive.Dispose() }
}

# Resumen
$items = Get-ChildItem -Force -LiteralPath $dest | Select-Object -ExpandProperty Name
$size = (Get-ChildItem -Recurse -File -Force -LiteralPath $dest | Measure-Object Length -Sum).Sum
Write-Output ''
Write-Output "Contenido de build\upload (esto es lo que va a public_html):"
$items | ForEach-Object { Write-Output "  - $_" }
Write-Output ('Total sin comprimir: {0:N1} MB' -f ($size / 1MB))
if (-not $NoZip) { Write-Output ('Zip: {0} ({1:N1} MB)' -f $zip, ((Get-Item -LiteralPath $zip).Length / 1MB)) }
