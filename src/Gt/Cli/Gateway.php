<?php
/**
 * Router script used by gtserver shell script. The gateway either returns the
 * bytes of a static file (when requested with a file extension) or passes the 
 * request on to the PHP.Gt Go initialiser object for processing.
 *
 * Will serve all directory-style URLs through PHP.Gt. Files (URLs with 
 * file extensions) will have their bytes streamed and correct HTTP headers set.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
require(__DIR__ . "/../../../vendor/autoload.php");

if(php_sapi_name() !== "cli-server") {
	echo "ERROR: Script must be called from cli-server.\n";
	exit(1);
}

$pathinfo = pathinfo($_SERVER["REQUEST_URI"]);
if(!empty($pathinfo["extension"])) {
	// Non-empty extension is served as static file.
	$request = explode("?", 
		$_SERVER["DOCUMENT_ROOT"]
		.
		$_SERVER["REQUEST_URI"]
	);

	if(!is_file($request[0])) {
		return false;
	}

	$ext = pathinfo($request[0], PATHINFO_EXTENSION);
	$finfo = new Finfo(FILEINFO_MIME_TYPE);
	$mime = $finfo->file($request[0]);

	header("Content-type: $mime");
	readfile($request[0]);
	return true;
}

// Request is to a PageView or WebService - pass responsibility to PHP.Gt core.
return new Gt\Core\Go();