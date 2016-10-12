<?php
namespace HBI;

use HBI\Exception\AutomationException;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverDimension;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\Exception;
use \Facebook\WebDriver\Exception\WebDriverException;

use \Facebook\WebDriver\Exception\UnknownServerException;

/**
*
*/
class HBIBrowserList
{
    public  $browserList;

    function __construct()
    {
        // We never want to have this happen twice
        SELF::createValidBrowserListByPlatform();
    }

    function __destruct()
    {
    }

    /**
     * [createValidBrowserListByPlatform description]
     * @return [type] [description]
     */
    private function createValidBrowserListByPlatform()
    {
        $list = array();
        print_r(PLATFORMS);
        print_r(BROWSERS);

        foreach (PLATFORMS as $p => $pw) {
            foreach (BROWSERS as $b => $bw) {
                if(SELF::isBrowserValid($b,$p)) {
                    $list[$p][$b] = $b;
                }
            }
        }
        $this->browserList = $list;
    }

    /**
     * [isBrowserValid description]
     * @param  [type]  $browserName [description]
     * @param  [type]  $platform    [description]
     * @return boolean              [description]
     */
    private function isBrowserValid($browserName, $platform)
    {
        // print sprintf('isBrowserValid: %s - %s'.PHP_EOL, $browserName, $platform);

        $dc  = DesiredCapabilities::$browserName();
        $hub = isset( SELENIUMHUB[$platform] ) ?  SELENIUMHUB[$platform] : $platform;
        try{
            $driver = RemoteWebDriver::create($hub, $dc, 5000);
            $driver->quit();

            unset($driver);
        } catch(WebDriverException $e) {
            return false;
        }

        return true;
    }

    /**
     * [setRandomBrowser description]
     */
    private function setRandomBrowser()
    {
        $p = $GLOBALS['platfrm'] ? $GLOBALS['platfrm'] : array_rand(SELENIUMHUB);

        return array_rand($this->browserList[$p]);
    }

    /**
     * [getBrowserListByPlatform description]
     * @param  [type] $platform [description]
     * @return [type]           [description]
     */
    private function getBrowserListByPlatform($platform)
    {
        return $this->browserList[$platform];
    }

    /**
     * [getWeightedBrowserListByPlatform description]
     * @param  [type] $platform [description]
     * @return [type]           [description]
     */
    private function getWeightedBrowserListByPlatform($platform)
    {
        $wlist = array();
        $blist = $this->getBrowserListByPlatform[$platform];

        foreach ($blist as $b) {
            $w = BROWSERS[$b];
            for ($i=0; $i<$w; $i++) {
                $wlist[] = $b;
            }
        }

        return $wlist;
    }

    private function setBrowserListToGlobal()
    {
        DEFINE("PLATFORMBROWSERLIST", $this->browserList);
    }
}
