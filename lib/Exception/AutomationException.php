<?php
namespace HBI\Exception;

use Exception;

class AutomationException extends Exception {

    private $results;

    /**
     * [__construct description]
     * @param [type] $message [description]
     * @param [type] $results [description]
     */
    public function __construct($message, $results = null) {
        parent::__construct($message);
        $this->results = $results;
    }

    /**
     * [getResults description]
     * @return [type] [description]
     */
    public function getResults() {
        return $this->results;
    }

    /**
     * [throwException description]
     * @param  [type] $status_code [description]
     * @param  [type] $message     [description]
     * @param  [type] $results     [description]
     * @return [type]              [description]
     */
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
