param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$ArtisanArguments = @('serve')
)

# Runs Laravel with a project-local CA bundle so Paystack HTTPS calls work on a
# local PHP install that has no curl.cainfo/openssl.cafile configured globally.
# This intentionally avoids changing the machine-wide php.ini.
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$caBundle = Join-Path $projectRoot 'certs/cacert.pem'
$artisan = Join-Path $projectRoot 'artisan'

if (-not (Test-Path -LiteralPath $caBundle)) {
    throw "CA bundle not found at $caBundle"
}

Push-Location -LiteralPath $projectRoot
try {
    $arguments = @('serve') + $ArtisanArguments

    & php '-d' "curl.cainfo=$caBundle" '-d' "openssl.cafile=$caBundle" $artisan @arguments
    exit $LASTEXITCODE
}
finally {
    Pop-Location
}
