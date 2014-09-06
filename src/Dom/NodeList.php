<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

class NodeList implements \Countable, \Iterator, \ArrayAccess {

private $iteratorIndex;
public $nodeArray;

public function __construct($domNodeList) {
	$this->nodeArray = [];

	if($domNodeList instanceof \DOMNodeList
	|| is_array($domNodeList)) {
		foreach($domNodeList as $domNode) {
			$node = new Node($domNode);
			$this->nodeArray []= $node;
		}
	}
	else {
		// TODO: The problem here is that is is being constructed as an instance of itself...
		var_dump($domNodeList);die();
		throw new InvalidNodeTypeException();
	}
}

// Countable -------------------------------------------------------------------
/**
 * Count the number of Node elements stored within this NodeList's $nodeArray.
 *
 * @return integer Number of elements contained by this NodeList
 */
public function count() {
	var_dump($this->nodeArray);
	die("COUNT!");
	return count($this->nodeArray);
}

// Iterator --------------------------------------------------------------------
/**
 *
 */
public function rewind() {
	$this->iteratorIndex = 0;
}

/**
 *
 */
public function valid() {
	return isset($this->_elArray[$this->iteratorIndex]);
}

/**
 *
 */
public function current() {
	return $this->_elArray[$this->iteratorIndex];
}

/**
 *
 */
public function key() {
	return $this->iteratorIndex;
}

/**
 *
 */
public function next() {
	++$this->iteratorIndex;
}

// ArrayAccess -----------------------------------------------------------------

/**
 *
 */
public function offsetExists($offset) {
	return isset($this->nodeArray[$offset]);
}

/**
 *
 */
public function offsetGet($offset) {
	return $this->nodeArray[$offset];
}

/**
 *
 */
public function offsetSet($offset, $value) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

/**
 *
 */
public function offsetUnset($offset) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

}#