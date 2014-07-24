<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Obj_Test extends \PHPUnit_Framework_TestCase {

public function testObjPropertyDoesNotExist() {
	$obj = new Obj();
	$this->assertObjectNotHasAttribute("attributeName", $obj);
}

public function testObjPropertyIsCreated() {
	$obj = new Obj();
	$obj->test = "testValue";

	$this->assertEquals("testValue", $obj->test);
}

public function testObjNestedPropertyIsCreated() {
	$obj = new Obj();
	$obj->test->nested = "nestedValue";

	$this->assertObjectHasAttribute("test", $obj);
	$this->assertEquals("nestedValue", $obj->test->nested);
}

public function testObjConstructs() {
	$obj = new Obj([
		"testProperty" => "testValue",
	]);
	$this->assertEquals("testValue", $obj->testProperty);
}

}#