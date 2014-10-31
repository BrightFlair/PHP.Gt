<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Response\Response;
use \Gt\Core\Path;
use \Gt\Core\DirectoryRecurser;

class FileOrganiser {

private $response;
private $manifest;
private $emptyHash;
private $assetWwwDir;

private $staticFingerprintFile;

public function __construct($response, Manifest $manifest) {
	$this->response = $response;
	$this->manifest = $manifest;

	$wwwDir = Path::get(Path::WWW);
	$this->emptyHash = str_pad("", 32, "0");

	$assetPath = Path::get(Path::ASSET);
	$assetDirName = substr($assetPath, strrpos($assetPath, "/") + 1);

	$this->assetWwwDir = "$wwwDir/$assetDirName";
	$this->staticFingerprintFile = $wwwDir . "/static-fingerprint";
}

/**
 * @param PathDetails $pathDetails Representation of client-side paths
 *
 * @return bool True if organiser has copied any files, false if no files have
 * been copied
 */
public function organise($pathDetails = []) {
	$copyCount = 0;
	$staticValid = true;

	// Performing the 10 steps as described here:
	// http://php.gt/docs/static-file-fingerprinting
	if(!file_exists($this->staticFingerprintFile)) {
		$staticValid = false;
	}

	if(!$this->response->production) {
		if(!$this->checkStaticValid()) {
			$staticValid = false;
		}
	}

	if(!$staticValid) {
		$this->purgeStaticFiles();
		$copyCount += $this->copyAsset();
	}

	if(!$this->manifest->checkValid()) {
		$callback = null;
		if($this->response->getConfigOption("client_minified")) {
			// Minify everything in www
			$callback = [new Minifier(), "minify"];
		}

		// Do copying of files...
		$copyCount += $this->copyCompile($pathDetails, $callback);
	}

	return !!($copyCount);
}

/**
 * Removes any static files found in the www directory and removes the static
 * file fingerprint.
 */
public function purgeStaticWwwFiles() {
	$assetPath  = Path::get(Path::ASSET);
	$scriptPath = Path::get(Path::SCRIPT);
	$stylePath  = Path::get(Path::STYLE);

	$assetDirName  = substr($assetPath,  strrpos($assetPath, "/") + 1);
	$scriptDirName = substr($scriptPath, strrpos($scriptPath, "/") + 1);
	$styleDirName  = substr($stylePath,  strrpos($styleDirName, "/") + 1);

	if(file_exists($this->staticFingerprintFile)) {
		unlink($this->staticFingerprintFile);
	}

	// Remove the Asset directory, and all directories that start with the
	// script & style directory name followed by a dash. (Script and Style
	// directories in the WWW directory have their fingerprint appended).
	foreach(new \DirectoryIterator(Path::get(Path::WWW)) as $item) {
		$filename = $item->getFilename();

		if($filename === $assetDirName
		|| strpos($filename, $scriptDirName) . "-" === 0
		|| strpos($filename, $styleDirName) . "-" === 0) {
			DirectoryRecurser::s($item->getPathname());
		}
	}
}

/**
 * Performs the copying from source directories to the www directory, compiling
 * files as necessary. For example, source LESS files need to be compiled to
 * public CSS files in this process.
 *
 * @param PathDetails $pathDetails
 * @param callable|null $callback The callable to pass output through before
 * writing to disk
 *
 * @return int Number of files copied
 */
public function copyCompile($pathDetails, $callback = null) {
	$copyCount = 0;

	foreach ($pathDetails as $pathDetail) {
		if(!is_dir(dirname($pathDetail["destination"]))) {
			mkdir(dirname($pathDetail["destination"]), 0775, true);
		}

		$output = Compiler::parse($pathDetail["source"]);
		if(!is_null($callback)) {
			$output = call_user_func_array($callback, [$output]);
		}

		file_put_contents(
			$pathDetail["destination"],
			$output
		);
		++$copyCount;
	}

	return $copyCount;
}

/**
 * Fingerprints the source Asset, Script and Style directory contents and
 * compares to the fingerprint cache in the www directory.
 *
 * @return bool True if the www static directory contents are valid,
 * false if they are not (or if they do not exist)
 */
public function checkStaticValid() {
	$wwwDir = Path::get(Path::WWW);

	$assetSrcDir  = Path::get(Path::ASSET);
	$scriptSrcDir = Path::get(Path::SCRIPT);
	$styleSrcDir  = Path::get(Path::STYLE);
	$assetWwwDir  = $wwwDir . "/" . substr($assetSrcDir, -strlen("asset"));

	if(!is_dir($assetSrcDir)
	&& !is_dir($scriptSrcDir)
	&& !is_dir($styleSrcDir)) {
		return true;
	}

	if(!file_exists($this->staticFingerprintFile)) {
		return false;
	}

	// Recursive fingerprint whole source directory.
	$staticWwwFingerprint = file_get_contents($this->staticFingerprintFile);

	$assetSrcFingerprint = $this->recursiveFingerprint([
		$assetSrcDir,
		$scriptSrcDir,
		$styleSrcDir,
	]);

	return ($staticWwwFingerprint === $assetSrcFingerprint);
}

/**
 * Copies the source asset directory to the www directory and stores a
 * fingerprint of the source directory in a separate public file, for use in
 * checkAssetValid().
 */
public function copyAsset() {
	$copyCount = 0;

	$wwwDir = Path::get(Path::WWW);
	$assetSrcDir = Path::get(Path::ASSET);
	$assetWwwDir = $wwwDir . "/" . pathinfo($assetSrcDir, PATHINFO_BASENAME);
	$this->staticFingerprintFile = $wwwDir . "/asset-fingerprint";

	if(!is_dir($assetSrcDir)) {
		return $copyCount;
	}

	$hash = "";
	$copyCount = 0;

	$hash = $this->recursiveFingerprint($assetSrcDir);
	DirectoryRecurser::walk(
		$assetSrcDir,
		[$this, "copyAssetCallback"],
		$copyCount
	);

	if(!is_dir(dirname($this->staticFingerprintFile))) {
		mkdir(dirname($this->staticFingerprintFile), 0775, true);
	}

	file_put_contents($this->staticFingerprintFile, $hash);
	return $copyCount;
}

/**
 *
 */
public function copyAssetCallback($file, $iterator, &$out) {
	if($file->isDir()) {
		return;
	}

	$source = $file->getPathname();
	$dest = $this->assetWwwDir . "/" . $iterator->getSubPathname();

	if(!is_dir(dirname($dest))) {
		mkdir(dirname($dest), 0775, true);
	}

	if(copy($source, $dest)) {
		$out++;
	}
}

/**
 * Recursively iterate over all files within given directories and build up a
 * hash of their contents and file names.
 *
 * @param string|array $dir Directory to iterate, or an array of directories
 *
 * @return string 32 character hash of directory's contents, or 32 zeros
 * indicating an empty or non-existant directory
 */
private function recursiveFingerprint($dir) {
	if(!is_array($dir)) {
		$dir = [$dir];
	}

	// Return a special zeroed hash for when there are no source directories.
	$noSourceDirectories = true;
	foreach ($dir as $d) {
		if(is_dir($d)) {
			$noSourceDirectories = false;
		}
	}
	if($noSourceDirectories) {
		return $this->emptyHash;
	}

	$hashArray = [];

	foreach ($dir as $d) {
		$hash = DirectoryRecurser::hash($d);
		if($hash === md5("")) {
			return $hash = "";
		}

		$hashArray []= $hash;
	}

	$hash = implode("", $hashArray);
	if($hash === md5("")) {
		return $this->emptyHash;
	}

	return $hash;
}

}#