# build-source-tarball.ps1 — Bundle source for upload to VPS.
# Run from D:\F\tkawen-com\
# Output: D:\F\tkawen-com\deploy-package\tkawen-com-src.tar.gz

$ErrorActionPreference = 'Stop'

$root = "D:\F\tkawen-com"
$out  = "$root\deploy-package\tkawen-com-src.tar.gz"
Push-Location $root

# tar exists on Windows 10+ as bsdtar
Write-Host "Creating $out ..."
& tar -czf $out `
    --exclude='target' `
    --exclude='.git' `
    --exclude='deploy-package/tkawen-com-src.tar.gz' `
    --exclude='screenshot*.png' `
    --exclude='screen-*.png' `
    --exclude='Cargo.lock' `
    Cargo.toml `
    src `
    assets `
    deploy-package `
    README.md `
    .gitignore

Pop-Location

$bytes = (Get-Item $out).Length
Write-Host ("Built: {0:N0} bytes ({1:N1} KB)" -f $bytes, ($bytes/1KB))
Write-Host ""
Write-Host "Next step: scp to VPS40 and run installer:"
Write-Host "  scp $out root@173.212.235.93:/tmp/"
Write-Host "  ssh root@173.212.235.93"
Write-Host "    cd /tmp && tar -xzf tkawen-com-src.tar.gz -C /tmp/tkawen-src && cd /tmp/tkawen-src"
Write-Host "    sudo bash deploy-package/install.sh"
