<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class Server_Test extends \PHPUnit_Framework_TestCase {

private $arguments;
private $approot;

public function setUp() {
	$this->approot = \Gt\Test\Helper::createTmpDir();

	$this->arguments = $this->getMock(
		"\Symfony\Component\Console\Input\ArgvInput", ["getOption"]);

}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->approot);
}

public function testArgsSet() {
	$map = [
		["approot", $this->approot],
		["port", 8089],
	];

	$this->arguments->method("getOption")
		->will($this->returnValueMap($map));

	$server = new Server($this->arguments, true);
	$this->assertContains("php -S=localhost:8089", $server->processOutput);
	$this->assertContains("-t={$this->approot}/www", $server->processOutput);
	$this->assertContains("/PHP.Gt/src/Cli/Gatekeeper.php",
		$server->processOutput);
}

}#