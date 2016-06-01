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
class HBIValidBrowsers
{

    private $_browsers;

    function __construct()
    {
        $this->_browsers = array();
        $this->_buildValidBrowserList();
    }

    public function getListOfValidBrowsers()
    {
        return $this->_browsers;
    }

    private function _buildValidBrowserList()
    {
        $browsers = $this->_getAvailableBrowsersList();

        foreach ($browsers as $b) {
            $dc = DesiredCapabilities::$b();
            try{
                $driver = RemoteWebDriver::create(QAHOST, $dc, 5000);
                $driver->quit();

                unset($driver);
            } catch(UnknownServerException $e) {
                print('Bad Browser: '.$b.PHP_EOL);
                continue;
            }

            $this->_browsers[] = $b;
        }
    }

    private function _getAvailableBrowsersList()
    {
        $browsers = array(
                        'chrome',
                        'firefox',
                        // 'internetExplorer',
                        'opera',
                        'safari',
                        'phantomjs'
                    );

        return $browsers;
    }
}
