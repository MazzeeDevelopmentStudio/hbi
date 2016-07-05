<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverBy;

use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\Admin\Actions;
use HBI\Utils\RandomStringGenerator;

/**
 *
 */
class AddMarketingCode extends Actions
{
    private $mktcode;

    function __construct(HBIBrowser $browser, $person=null, $type=null)
    {
        $this->browser = $browser;
        $this->log     = $GLOBALS['HBILog'];
        $this->mktcode = new stdClass();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->code, true) );
    }

    protected function generateRandomMarketingCode()
    {
        $rnd = rand(0,10);
        if(!$rnd) {
            SELF::getEdgeCaseCondition();
        }

        $len = rand(3,15);

    }

    protected function getEdgeCaseCondition()
    {

    }
}
