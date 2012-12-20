<?php final class Request {
/**
 * Deals with all elements of a page request. The URL is dissected to define
 * which PageViews and PageCodes to load, or which API to serve, depending on
 * extension. If the isCached setting is enabled, the cached version of the 
 * current request will be used if valid.
 */
public $api = null;
public $pageCode = null;
public $pageCodeCommon = array();
public $contentType;

// Any page code can set this to true to stop executing any more page codes.
public $pageCodeStop = false;

public function __construct($config) {
	$this->contentType = "text/html";
	session_start();

	if($config["App"]->isProduction()) {
		if(isset($_SESSION["PhpGt_Development"])) {
			unset($_SESSION["PhpGt_Development"]);
		}
	}
	else {
		$_SESSION["PhpGt_Development"] = true;
	}

	// Allow language selection through special URI syntax.
	$bd = BASEDIR;
	if(substr($bd, 0, 1) === "_") {
		$lang = substr($bd, 1);

		if(strlen($lang) > 0) {
			// Save the language in session for output to html metadata, and for
			// use in apps.
			$_SESSION["PhpGt_Lang"] = ["Code" => $lang];
		}
		else {
			unset($_SESSION["PhpGt_Lang"]);
		}

		$uri = $_SERVER["REQUEST_URI"];
		$uri = str_replace("_{$lang}/", "", $uri);

		http_response_code(302);
		header("Location: $uri");
		exit;
	}

	// Useful for faking slow connections on AJAX calls.
	if(isset($_GET["FakeSlow"])) {
		$seconds = empty($_GET["FakeSlow"])
			? 2 : $_GET["FakeSlow"];
		sleep($seconds);
	}

	if(EXT == "json") { 
		$this->contentType = "application/json";

		// Look for requested API. Note that API requests have to always
		// be in the root directory i.e. /Blog.json, and can never be nested
		// i.e. /Blog/2010/01/Blog.json
		$apiName   = ucfirst(FILE);
		$className = $apiName . "_Api";
		$fileName  = $apiName . ".api.php";
		$apiPathArray = array(
			APPROOT . DS . "Api" . DS,
			GTROOT  . DS . "Api" . DS
		);
		foreach ($apiPathArray as $path) {
			if(file_exists($path . $fileName)) {
				require_once($path . $fileName);
				break;
			}
		}
		if(class_exists($className)) {
			$this->api = new $className();
			$data = $_REQUEST;
			if(isset($data["url"])) {
				unset($data["url"]);
			}
			if(isset($data["ext"])) {
				unset($data["ext"]);
			}
			if(!isset($data["Method"])) {
				$this->api->setError("API method not specified.");
				return;
			}

			$methodName = $data["Method"];
			unset($data["Method"]);
			$this->api->setMethodName($methodName);
			$this->api->setApiName($apiName);

			$paramArray = array();
			foreach ($data as $key => $value) {
				$paramArray[$key] = $value;
			}
			$this->api->setMethodParams($paramArray);

			if(!method_exists($this->api, lcfirst($methodName)) &&
			!in_array(ucfirst($methodName), $this->api->externalMethods)) {
				$this->api->setError("Given method either does not exist "
					. "or requires more parameters.");
				return;
			}
		}
		else {
			$this->api = new Api();
			$this->api->setApiName("PhpGt_API_Error");
			$this->api->setError("Requested API does not exist.");
			return;
		}
	}
	else {
		// Look for common PageCode for current directory, also work up the
		// directory tree and look for and execute higher PageCodes.
		$pcClassSuffix = "_PageCode";
		$pcDirArray = array();
		$pcBaseDir = APPROOT . DS . "PageCode" . DS;
		$filePathArray = explode("/", DIR);
		for($i = 0; $i < count($filePathArray); $i++) {
			$prefix = "";
			foreach ($pcDirArray as $pcDir) {
				$prefix .= $pcDir . DS;
			}

			$pcDirArray[] = $prefix . $filePathArray[$i];
		}
		$pcDirArray = array_reverse($pcDirArray);
		if(!in_array("", $pcDirArray)) {
			$pcDirArray[] = "";
		}

		// $pcDirArray now contains at least 1 element, which is the
		// relative directory of the current request, plus the relative
		// directories moving up the tree to the root directory.
		// For example: /Shop/NewItems/Item-1.html will become array(
		// 0 => 'Shop/NewItems', 1 => 'Shop')

		// Reverse array so that common PageCodes are executed in tree order.
		$pcDirArray = array_reverse($pcDirArray);

		foreach ($pcDirArray as $pcDir) {
			$pcCommonPath  = APPROOT . DS . "PageCode" . DS . $pcDir . DS;
			$pcCommonFile  = "_Common.php";
			$pcCommonClass = str_replace("/", "_", $pcDir) 
				. "_Common";
			if(file_exists($pcCommonPath . $pcCommonFile)) {
				require_once($pcCommonPath . $pcCommonFile);
				if(class_exists($pcCommonClass)) {
					$this->pageCodeCommon[] = 
						new $pcCommonClass($this->pageCodeStop);
				}
				else if(class_exists($pcCommonClass . $pcClassSuffix)) {
					$pcWithSuffix = $pcCommonClass . $pcClassSuffix;
					$this->pageCodeCommon[] = new $pcWithSuffix(
						$this->pageCodeStop);
				}
			}
		}

		//  for and load the page's specific PageCode.
		$pageCodeFile  = APPROOT . DS . "PageCode" . DS . FILEPATH . ".php";
		$pageCodeClass = FILECLASS;
		if(file_exists($pageCodeFile)) {
			require($pageCodeFile);
			if(class_exists($pageCodeClass)) {
				$this->pageCode = new $pageCodeClass($this->pageCodeStop);
			}
			else if(class_exists($pageCodeClass . $pcClassSuffix)) {
				$pcWithSuffix = $pageCodeClass . $pcClassSuffix;
				$this->pageCode = new $pcWithSuffix($this->pageCodeStop);
			}
		}
	}

	// Check whether whole request is cached.
	if($config["App"]->isCached()) {
		// ALPHATODO:
		// TODO: Cache output.
		// A file per URL can be created in the Cache directory. This file's
		// filemtime signifies whether the URL is cached. This can be modified
		// from within code, or even with a MySQL trigger!
	}

	// Check for framework-reserved requests.
	if(in_array(FILE, $config["App"]->getReserved())
	|| in_array(BASEDIR, $config["App"]->getReserved() )) {

		// Request is reserved, pass request on to the desired function.
		$reservedName = BASEDIR == ""
		? FILE
		: BASEDIR;
		$reservedFile = GTROOT . DS . "Framework" . DS 
			. "Reserved" . DS . ucfirst($reservedName) . ".php";
		if(file_exists($reservedFile)) {
			require($reservedFile);
			$reservedClassName = $reservedName . "_Reserved";
			if(class_exists($reservedClassName)) {
				new $reservedClassName($config);
			}
			exit;
		}
		die("Reserved");
	}

	return;
}

}?>