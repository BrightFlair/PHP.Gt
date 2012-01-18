<?php
class DomEl implements ArrayAccess {
	public $node;
	private $_dom;

	/**
	* A wrapper to PHP's native DOMElement, adding more object oriented
	* features to be more like JavaScript's implementation.
	*/
	public function __construct(
	$dom,
	$element,
	$attrArray  = null,
	$value      = null) {

		$this->_dom = $dom;

		if($element instanceof DOMElement) {
			$this->node = $element;
		}
		else if(is_string($element)) {
			// TODO: New feature: Allow passing in CSS selector to create
			// the element, i.e. create("div.product.selected");
			$this->node = $this->_dom->getDomDoc()->createElement(
				$element, $value);
		}

		if(is_array($attrArray)) {
			foreach($attrArray as $key => $value) {
				$this->node->setAttribute($key, $value);
			}
		}
	}

	public function offsetExists($selector) {
		
	}

	public function offsetGet($selector) {
		return $this->_dom->offsetGet($selector, $this->node);
	}

	public function offsetSet($selector, $value) {
		// TODO: Does this need to be implemented?
	}

	public function offsetUnset($selector) {
		// TODO: Remove item's children matching the selector.
	}

	/**
	* TODO: Docs.
	*/
	public function append($toAppend) {
		$elementArray = array();

		if(is_array($toAppend) || $toAppend instanceof DomElCollection) {
			$elementArray = $toAppend;
		}
		else if(is_string($toAppend)) {
			$attrArray = null;
			$value = null;
			$args = func_get_args();
			if(isset($args[1])) {
				$attrArray = $args[1];
			}
			if(isset($args[2])) {
				$value = $args[2];
			}

			$elementArray[] = new DomEl(
				$this->_dom,
				$toAppend,
				$attrArray,
				$value
			);
		}
		else {
			$elementArray[] = $toAppend;
		}

		foreach($elementArray as $element) {
			$elNode = $element;
			if($element instanceof DomEl) {
				$elNode = $element->node;
			}

			$this->node->appendChild($elNode);
		}
	}

	/**
	 * Appends multiple elements to this element, taking values from the
	 * array passed in. This element will have however many indeces are in the
	 * array appended elements.
	 * @param mixed $array The array of data to compute, or an enumerable 
	 * object.
	 * @param mixed $element The element to create and append for each item in
	 * the array.
	 * @param array $attrArray A key-value-pair of attribute names and array 
	 * keys. Each key will be created as an attribute on the new element,
	 * the attribute's value will be the value stored in $array's index that
	 * matches the value of the $attrArray key.
	 * @param string $textKey The index of each $array element to use as the
	 * node to append's text value.
	 */
	public function appendArray($data, $element,
	$attrArray = array(), $textKey = null) {
		$elementToCreate = null;

		if($element instanceof DOMNode) {
			$elementToCreate = new DomEl(
				$this->_dom, 
				$element->cloneNode(true));
		}
		else if($element instanceof DOMEl) {
			$elementToCreate = $element->cloneNode();
		}
		else if(is_string($element)) {
			$elementToCreate = new DomEl($this->_dom, $element);
		}

		foreach ($data as $item) {
			$clonedElement = $elementToCreate->cloneNode();

			foreach ($attrArray as $key => $value) {
				if(isset($item[$value])) {
					$clonedElement->setAttribute($key, $item[$value]);
				}
			}

			if(!is_null($textKey)) {
				if(isset($item[$textKey])) {
					$clonedElement->innerText = $item[$textKey];
				}
			}

			$this->append($clonedElement);
		}
	}

	/**
	* TODO: Docs.
	*/
	public function remove() {
		$this->node->parentNode->removeChild($this->node);
	}

	/**
	 * TODO: Docs.
	 */
	public function cloneNode($deep = true) {
		return new DomEl($this->_dom, $this->node->cloneNode($deep));
	}

	/**
	 * TODO: Docs.
	 */
	public function addClass($className) {
		$stringArray = array();

		if(is_array($className)) {
			$stringArray = $className;
		}
		else {
			$stringArray = array($className);
		}

		$currentClass = $this->node->getAttribute("class");

		foreach($stringArray as $string) {
			$currentClass .= " " . $string;
		}

		$this->node->setAttribute("class", $currentClass);
	}

	/**
	 * TODO: Docs.
	 */
	public function removeClass($className) {
		$stringArray = array();

		if(is_array($className)) {
			$stringArray = $className;
		}
		else {
			$stringArray = array($className);
		}

		$currentClass = $this->node->getAttribute("class");

		foreach($stringArray as $string) {
			// Remove any occurence of the string, with optional spaces.
			$currentClass = preg_replace(
				"/\s?" . $string . "\s?/",
				"",
				$currentClass);
		}

		$this->node->setAttribute("class", $currentClass);
	}

	/**
	* TODO: Docs.
	*/
	public function __call($name, $args = array()) {
		if(method_exists($this->node, $name)) {
			return call_user_func_array(array($this->node, $name), $args);
		}
		else {
			return false;
		}
	}

	/**
	* TODO: Docs.
	*/
	public function __get($key) {
		switch($key) {
		case "innerHTML":
		case "innerHtml":
		case "innerText":
			return $this->node->nodeValue;
			break;
		default: 
			if(property_exists($this->node, $key)) {
			// Attempt to never pass a native DOMElement without converting to
			// DomEl wrapper class.
			if($this->node->$key instanceof DOMELement) {
			return $this->_dom->create($this->node->$key);
			}
			return $this->node->$key;
			}
			break;
		}
	}

	/**
	* TODO: Docs.
	*/
	public function __set($key, $value) {
		switch($key) {
		case "innerHTML":
		case "innerHtml":
		case "innerText":
			$this->node->nodeValue = $value;
			break;
		default:
			$this->node->setAttribute($key, $value);
			break;
		}
	}
}
?>