<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

use \Gt\Core\Path;
use \Gt\Request\Request;

class LogicFactory {

/**
 * @param string $appNamespace Base namespace containing all application logic
 * @param string $uri The current request URI
 * @param ApiFactory $apiFactory API Access Layer
 * @param DatabaseFactory $dbFactory Database Access Layer
 *
 * @return array An array containing all appropriate Logic objects,
 * depending on request type, in execution order
 */
public static function create($appNamespace, $uri,
$apiFactory, $dbFactory, $content) {
	$objArray = [];
	$topPath = Path::get(Path::PAGE);

	$filename = basename($uri);
	$path = pathinfo($topPath . $uri, PATHINFO_DIRNAME);
	$logicFileArray = self::getLogicFileArray(
		$filename, $path, $topPath);
	$logicClassNameArray = self::getLogicClassNameArray(
		$appNamespace, $logicFileArray, $topPath);

	foreach ($logicClassNameArray as $className) {
		if(!class_exists($className)) {
			continue;
		}

		$objArray []= new $className(
			$apiFactory,
			$dbFactory,
			$content
		);
	}

	return $objArray;
}

/**
 * Return an array of absolute filepaths to the logic files that match the
 * current URI, in order of execution.
 *
 * @param string $filename The requested filename, without any directory path
 * @param string $path The path to look for logic files in, moving up the tree
 * until and including the $topPath path
 * @param string $topPath The top-most path to use when looking for logic files
 *
 * @return array An array of file paths for each potential logic file, even if
 * there is no file at that path.
 */
public static function getLogicFileArray($filename, $path, $topPath) {
	// Get PageLogic path for current URI.
	$currentPageLogicPath = implode("/", [$path, $filename]) . ".php";
	$commonPageLogicPathArray = [];

	$currentPath = $path;
	while(false !== strstr($currentPath, $topPath)) {
		$commonPageLogicPathArray [] = $currentPath . "/_common.php";
		$currentPath = dirname($currentPath);
	}

	$commonPageLogicPathArray = array_reverse($commonPageLogicPathArray);
	$pageLogicPathArray = array_merge(
		$commonPageLogicPathArray,
		[$currentPageLogicPath]
	);

	// Check the case of each Page Logic file
	foreach ($pageLogicPathArray as $i => $path) {
		$pageLogicPathArray[$i] = Path::fixCase($path);
	}

	return $pageLogicPathArray;
}

/**
 * Return an array of Logic class names, in correct execution order,
 * to hand to the Dispatcher where their go methods will be called at the
 * correct time. Each element in the array is fully qualified with the
 * namespace it is contained within.
 *
 * @param string $appNamespace Base namespace containing all application logic
 * @param array $logicPathArray Array of absolute file paths to all Logic
 * classes on disk
 * @param string $topPath The top-most path to use when looking for logic files
 *
 * @return array Array of instantiated Logic objects
 */
public static function getLogicClassNameArray(
$appNamespace, $logicPathArray, $topPath) {
	$classNameArray = [];
	$srcPath = Path::get(Path::SRC);

	foreach ($logicPathArray as $logicPath) {
		$namespaceStr = substr($logicPath, strlen($srcPath) + 1);
		$namespaceArray = explode("/", $namespaceStr);
		array_unshift($namespaceArray, $appNamespace);

		$className = strtok(basename($logicPath), ".");
		// $classNameArray []=
		var_dump(
			implode("\\", array_merge($namespaceArray, [$className]))
			);die();
	}

	var_dump($classNameArray);die();
	return $classNameArray;
}

}#