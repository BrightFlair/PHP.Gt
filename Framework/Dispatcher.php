<?php
/** TODO:
* The purpose of this is to dispatch the events to the PageCode, which is
* stored in the Request, which is stored in the Response, which is passed in
* to this constructor... :S
*/
final class Dispatcher {
	public function __construct($response, $config) {
		$response->dispatch("init");
		$pageTools = $response->dispatch("getTools");

		$appConfig = $config["App"];

		$dal = new Dal($config["Database"]->getSettings());
		if($response->dispatch("apiCall", $dal)) {
			// Quit early if request is api call.
			$response->dispatch("apiOutput");
			return;
		}

		$apiWrapper = new ApiWrapper($dal);

		if(count($_POST) > 0) {
			$response->dispatch("onPost");
		}

		$getData = $_GET;
		if(array_key_exists("url", $getData)) {
			// On root URLs, the query string may be used as the "url" key.
			if(strstr($getData["url"], "?")) {
				$keyValuePair = substr($getData["url"],
					strpos($getData["url"], "?") + 1);
				if(is_string($keyValuePair)) {
					$getData[$keyValuePair] = "";
				}
				else {
					$keyValuePair = explode("=", $keyValuePair);
					$getData[$keyValuePair[0]] = $keyValuePair[1];
				}
			}
			unset($getData["url"]);
		}
		if(array_key_exists("ext", $getData)) {
			unset($getData["ext"]);
		}

		$_GET = $getData;

		if(count($getData) > 0) {
			$response->dispatch("onGet");
		}

		$response->dispatch("main", $apiWrapper);

		$dom = new Dom($response->getBuffer());
		$response->includeDom($dom);


		// Call a preRender function for backwards compatibility - this function
		// is not needed at all though any more.
		$response->dispatch("preRender", $dom);

		$organiser = new FileOrganiser();

		$isCompiled = $appConfig->isClientCompiled();
		$injector  = new Injector($dom, $isCompiled);

		$template = $dom->template();
		$response->executePageTools($pageTools, $apiWrapper, $dom, $template);
		$response->dispatch("render", $dom, $template, $injector);
		$response->dispatch("go", $apiWrapper, $dom, $template);

		$dom->flush();
	}
}
?>