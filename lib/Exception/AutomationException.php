<?php
namespace HBI\Exception;

use Exception;

class AutomationException extends Exception {

    private $results;

    /**
    * @param string $message
    * @param mixed $results
    */
    public function __construct($message, $results = null) {
        parent::__construct($message);
        $this->results = $results;
    }

    /**
    * @return mixed
    */
    public function getResults() {
        return $this->results;
    }

    public static function throwException($status_code, $message, $results)
    {
        switch ($status_code) {
            case -1:
                throw new Exception($message);
            case 0:
                // Success
                break;
            case 1:
                throw new Exception($message, $results);
            default:
                throw new Exception($message, $results);
        }
    }
}
