# Remove legacy admin shim files (force, no confirmation)
# Usage: Run from project root in PowerShell: .\scripts\remove_admin_shims.ps1

$files = @(
    "pages\admin\dashboard_admin.php",
    "pages\admin\produk_add.php",
    "pages\admin\produk_edit.php",
    "pages\admin\produk_delete.php"
)

Write-Host "Removing legacy admin shim files..."
foreach ($f in $files) {
    $full = Join-Path (Get-Location) $f
    if (Test-Path $full) {
        try {
            Remove-Item -Path $full -Force -ErrorAction Stop
            Write-Host "Removed: $f"
        } catch {
            Write-Warning "Failed to remove $f : $_"
        }
    } else {
        Write-Host "Not found (already removed): $f"
    }
}

Write-Host "Cleanup complete. Backups are available in 'archive_before_cleanup/pages/admin/'."
