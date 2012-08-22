<?php class Analytics_PageTool extends PageTool {
/**
 * Google Analytics.
 * This simple PageTool doesn't have any functionality in the go() method.
 * Instead, pass the tracking code into the track() method.
 */
public function go($api, $dom, $template, $tool) { }

/**
 * Injects the required JavaScript code where needed to start tracking using
 * Google Analytics.
 *
 * @param string $trackingCode Your Google Analytics account code, looks like 
 * this: UA-12345678-1
 */
public function track($trackingCode) {
	$js = file_get_contents(dirname(__FILE__) . DS . "Analytics.tool.js");
	if($js === false) {
		throw new HttpError(500, "Google Analytics script failure");
	}
	$js = str_replace("{ANALYTICS_CODE}", $trackingCode, $js);

	//$script = $this->_dom->create("script", null, $js);
	$this->_dom["head"]->append("script", null, $js);
}

}?>