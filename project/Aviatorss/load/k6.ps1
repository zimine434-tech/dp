# Обёртка для k6. Читает K6_* и BASE_URL из корневого .env и load/.env.
# Примеры: .\load\k6.ps1 run load/login-load.js

function Import-DotEnvFile {
    param(
        [string]$Path,
        [string[]]$OnlyKeys = @()
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        return
    }

    Get-Content -LiteralPath $Path -Encoding UTF8 | ForEach-Object {
        $line = $_.Trim()
        if ($line -eq '' -or $line.StartsWith('#')) {
            return
        }
        $eq = $line.IndexOf('=')
        if ($eq -lt 1) {
            return
        }
        $name = $line.Substring(0, $eq).Trim()
        if ($OnlyKeys.Count -gt 0 -and $name -notin $OnlyKeys) {
            return
        }
        $value = $line.Substring($eq + 1).Trim()
        if ($value.StartsWith('"') -and $value.EndsWith('"')) {
            $value = $value.Substring(1, $value.Length - 2)
        }
        if ($value -eq '') {
            return
        }
        Set-Item -Path "Env:$name" -Value $value
    }
}

function Get-K6EnvPassThroughArgs {
    $out = @()
    foreach ($key in @('BASE_URL', 'K6_LOGIN', 'K6_PASSWORD', 'K6_VUS')) {
        $val = [Environment]::GetEnvironmentVariable($key)
        if ($val) {
            $out += '-e'
            $out += "${key}=$val"
        }
    }
    return $out
}

$scriptDir = $PSScriptRoot
$projectRoot = Split-Path -Parent $scriptDir
$projectEnvPath = Join-Path $projectRoot '.env'
$loadEnvPath = Join-Path $scriptDir '.env'

$k6Keys = @('BASE_URL', 'K6_LOGIN', 'K6_PASSWORD', 'K6_VUS')

Import-DotEnvFile -Path $projectEnvPath -OnlyKeys $k6Keys
Import-DotEnvFile -Path $loadEnvPath

$k6Paths = @(
    'C:\Program Files\k6\k6.exe',
    "${env:ProgramFiles}\k6\k6.exe",
    "${env:ProgramFiles(x86)}\k6\k6.exe"
)

$k6Exe = $null
foreach ($path in $k6Paths) {
    if (Test-Path -LiteralPath $path) {
        $k6Exe = $path
        break
    }
}

if (-not $k6Exe) {
    $found = Get-Command k6 -ErrorAction SilentlyContinue
    if ($found) {
        $k6Exe = $found.Source
    }
}

if (-not $k6Exe) {
    Write-Error 'k6 ne naiden. Ustanovite: winget install GrafanaLabs.k6'
    exit 1
}

$joinedArgs = ($args -join ' ')
if ($joinedArgs -match 'login-|teacher-' -and (-not $env:K6_LOGIN -or -not $env:K6_PASSWORD)) {
    Write-Host ''
    Write-Host 'Nuzhny K6_LOGIN i K6_PASSWORD v .env (koren proekta ili load\.env).' -ForegroundColor Yellow
    Write-Host ''
}

$k6Args = [System.Collections.Generic.List[string]]::new()
if ($args.Count -gt 0 -and $args[0] -eq 'run') {
    $k6Args.Add('run')
    foreach ($ea in (Get-K6EnvPassThroughArgs)) {
        $k6Args.Add($ea)
    }
    for ($i = 1; $i -lt $args.Count; $i++) {
        $k6Args.Add($args[$i])
    }
}
else {
    $k6Args.AddRange($args)
}

& $k6Exe $k6Args.ToArray()
exit $LASTEXITCODE
