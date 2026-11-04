param(
    [ValidateSet('start', 'stop', 'status')]
    [string]$Action = 'start'
)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$serverScript = (Resolve-Path (Join-Path $projectRoot 'chat/server/run.php')).Path
$serverDirectory = Split-Path $serverScript
$pidFile = Join-Path $serverDirectory 'server.pid'
$serverProcess = $null
if (Test-Path -LiteralPath $pidFile) {
    $savedId = 0
    if ([int]::TryParse((Get-Content -LiteralPath $pidFile -Raw).Trim(), [ref]$savedId)) {
        $candidate = Get-CimInstance Win32_Process -Filter "ProcessId = $savedId" -ErrorAction SilentlyContinue
        # Verificar el comando evita detener un proceso ajeno si Windows reutilizó el PID.
        if ($candidate -and $candidate.Name -eq 'php.exe' -and $candidate.CommandLine.IndexOf($serverScript, [StringComparison]::OrdinalIgnoreCase) -ge 0) {
            $serverProcess = Get-Process -Id $savedId -ErrorAction SilentlyContinue
        }
    }
}
if ($Action -eq 'stop') {
    if ($serverProcess) { Stop-Process -Id $serverProcess.Id; Write-Output 'WebSocket detenido. El chat continúa por AJAX.' }
    else { Write-Output 'No hay servidor del chat iniciado por este comando.' }
    if (Test-Path -LiteralPath $pidFile) { Remove-Item -LiteralPath $pidFile }
    exit
}
if ($Action -eq 'status') {
    if ($serverProcess) { Write-Output "WebSocket activo. PID: $($serverProcess.Id)" }
    else { Write-Output 'WebSocket detenido. El chat usa AJAX.' }
    exit
}
if ($serverProcess) { Write-Output "WebSocket ya está activo. PID: $($serverProcess.Id)"; exit }
$phpExecutable = (Get-Command php -CommandType Application | Select-Object -First 1).Source
$started = Start-Process -FilePath $phpExecutable -ArgumentList ('"' + $serverScript + '"') -WorkingDirectory $projectRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $serverDirectory 'server.log') -RedirectStandardError (Join-Path $serverDirectory 'error.log')
Start-Sleep -Milliseconds 700
if ($started.HasExited) { throw 'No se pudo iniciar. Revisá chat/server/error.log (migración, dependencias o puerto ocupado).' }
Set-Content -LiteralPath $pidFile -Value $started.Id
Write-Output "WebSocket iniciado en segundo plano. PID: $($started.Id). Registro: chat/server/server.log"
