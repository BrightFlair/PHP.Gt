<?php class GtError extends Exception {

public function __construct($message = null) {
	throw new HttpError(500, $message);
}

}?>