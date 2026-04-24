$dest = "C:\Users\DELL\Documents\Lab\ProjetsClient\sacochechic\schic\public\images"

# Test: can we create ANY file/dir in public?
Write-Host "Trying to create test dir..."
New-Item -ItemType Directory -Path "$dest-test" -Force | Out-Null
Write-Host "Test dir exists: $(Test-Path "$dest-test")"
Get-ChildItem "C:\Users\DELL\Documents\Lab\ProjetsClient\sacochechic\schic\public" | Select-Object Name
