<?php
/**
 * Used as a wrapper to the PHP built-in server to handle directory paths and
 * alert the developer if directories do not exist, before starting the server.
 *
 * Used from the PHP.Gt/bin/server shell script.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

use \Symfony\Component\Console\Input\ArgvInput;

final class Server {

private $gtroot;
private $approot;
private $port;
private $process;
public $processOutput;

public static $contentTypeDefault = "text/plain";
public static $contentType = [
	"css"	=> "text/css",
	"js"	=> "application/javascript",
	"txt"	=> "text/plain",
];

/**
 * Sets the gtroot (allowing Gatekeeper to be found), and sets approot and port
 * with values from the ArgvInput object, then creates and runs the php
 * inbuilt server in a new Process.
 *
 * @param ArgvInput $arguments The arguments passed to the server shell script
 * or default values if none are provided.
 */
public function __construct($arguments, $dummyRun = false) {
	$this->gtroot = dirname(__DIR__);
	$this->approot = $arguments->getOption("approot");
	$this->port = $arguments->getOption("port");

	$overrideApproot = $arguments->getArgument("approot");
	if(!is_null($overrideApproot)) {
		$this->approot = $overrideApproot;
	}

	$wwwDir = "{$this->approot}/www";
	if(!is_dir($wwwDir)) {
		mkdir($wwwDir, 0775, true);
	}

	$this->process = new Process(
		"php", [
		"S" => "localhost:{$this->port}",
		"t" => $wwwDir,
		"{$this->gtroot}/Cli/Gatekeeper.php",
	]);

	$this->processOutput = $this->process->run($dummyRun);
}

}#