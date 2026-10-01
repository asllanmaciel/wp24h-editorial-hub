[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$repositoryRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$pluginSource = $repositoryRoot
$artifactDirectory = Join-Path $repositoryRoot 'dist'
$packagePath = Join-Path $artifactDirectory 'wp24h-editorial-hub-1.0.9.zip'
$temporaryRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('wp24h-editorial-hub-' + [Guid]::NewGuid().ToString('N'))
$stagingPlugin = Join-Path $temporaryRoot 'wp24h-editorial-hub'

New-Item -ItemType Directory -Path $stagingPlugin -Force | Out-Null

try {
	Copy-Item -LiteralPath (Join-Path $pluginSource 'wp24h-editorial-hub.php') -Destination $stagingPlugin
	Copy-Item -LiteralPath (Join-Path $pluginSource 'readme.txt') -Destination $stagingPlugin
	Copy-Item -LiteralPath (Join-Path $pluginSource 'LICENSE') -Destination $stagingPlugin
	Copy-Item -LiteralPath (Join-Path $pluginSource 'src') -Destination $stagingPlugin -Recurse
	Copy-Item -LiteralPath (Join-Path $pluginSource 'assets') -Destination $stagingPlugin -Recurse

	New-Item -ItemType Directory -Path $artifactDirectory -Force | Out-Null
	Remove-Item -LiteralPath $packagePath -Force -ErrorAction SilentlyContinue
	Add-Type -AssemblyName System.IO.Compression
	Add-Type -AssemblyName System.IO.Compression.FileSystem
	$archive = [System.IO.Compression.ZipFile]::Open($packagePath, [System.IO.Compression.ZipArchiveMode]::Create)

	try {
		Get-ChildItem -LiteralPath $stagingPlugin -Recurse -File | Sort-Object FullName | ForEach-Object {
			$relativePath = $_.FullName.Substring($stagingPlugin.Length).TrimStart('\', '/').Replace('\', '/')
			$entryName = 'wp24h-editorial-hub/' + $relativePath
			[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
				$archive,
				$_.FullName,
				$entryName,
				[System.IO.Compression.CompressionLevel]::Optimal
			) | Out-Null
		}
	}
	finally {
		$archive.Dispose()
	}

	Write-Output "Created $packagePath"
}
finally {
	if (Test-Path $temporaryRoot) {
		Remove-Item -LiteralPath $temporaryRoot -Recurse -Force
	}
}
