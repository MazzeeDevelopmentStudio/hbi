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

/**
*
*/
class HBIBrowser
{
    private $_capabilities;
    private $_driver;
    private $_window;
    private $_panel;
    private $_webui;

    function __construct($browser = "firefox")
    {
        if($browser == 'random') {
            $browser = $this->setRandomBrowser();
        }

        $this->_capabilities = DesiredCapabilities::$browser();
        $this->_driver       = RemoteWebDriver::create(QAHOST, $this->_capabilities, 5000);
        $this->_window       = New WebDriverWindow($this->_driver);
        $this->_panel        = new HBIPanel($this->_driver);
        $this->_webui        = new HBIWebUI($this->_driver);
    }

    function __destruct()
    {
        unset($this->_webui);
        unset($this->_panel);
        unset($this->_window);
        unset($this->_driver);
        unset($this->_capabilities);
    }

    public function driver()
    {
        return $this->_driver;
    }

    public function window()
    {
        return $this->_window;
    }

    public function panel()
    {
        return $this->_panel;
    }

    public function webui()
    {
        return $this->_webui;
    }

    public function openURL($url)
    {
        $this->_driver->get($url);
    }

    public function makeNewDimensions($d)
    {
        $dimensions = new \stdClass();

        if( empty($d) ) {
            $dimensions->random = true;
            $dimensions->width  = 480;
            $dimensions->height = 640;

            return $dimensions;
        }

        $dimensions->random = false;
        $dimensions->width  = $d['width'];
        $dimensions->height = $d['height'];


        return $dimensions;
    }

    public function setRandomBrowser()
    {
        $browsers = $this->getBrowserList();
        shuffle($browsers);

        foreach ($browsers as $b) {
            error_log( sprintf('Trying Browser: %s'.PHP_EOL, $b) );
            $dc = DesiredCapabilities::$b();
            try{
                $driver = RemoteWebDriver::create(QAHOST, $dc, 5000);
                $driver->quit();

                unset($driver);
            } catch(WebDriverException $e) {
                // error_log( sprintf('Browser is not valid: %s %s %s', $b, PHP_EOL, print_r($e,true)) );
                continue;
            }

            return $b;
        }

        return false;
    }

    public function getBrowserList()
    {
        $browsers = array(
                        'chrome',
                        'firefox',
                        'internetExplorer',
                        'opera'
                        // 'safari'
                        // 'phantomjs'
                    );

        return $browsers;

    }

    public function maximizeWindow()
    {
        $this->_window->maximize();
    }

    /**
     * [reSizeWindow description]
     * @param  Object $dimensions [description]
     * @return [type]             [description]
     */
    public function reSizeWindow($dimensions)
    {
        $wdd = new WebDriverDimension($dimensions->width, $dimensions->height);

        $this->_window->setSize($wdd);
    }

    public function clickElement(WebDriverBy $by)
    {
        // Get a single element from a list of one or more elements
        $el = $this->_webui->getOneOfManyElements($by);

        if(empty($el)) {
            throw new AutomationException("Element Not Found");
        }

        // Bring it to view
        $el->getLocationOnScreenOnceScrolledIntoView();

        $this->_driver->wait(5, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($el)
        );

        // click it
        $el->click();
    }

    public function waitForElement(WebDriverBy $by)
    {
        $this->_driver->wait(20, 1000)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($by)
        );
    }

    public function quitBrowser()
    {
        $this->_driver->quit();
    }

    public function addContentToFormField(WebDriverBy $by, $content)
    {
        $this->_webui->enterFieldData(
            $by,
            $content
        );

    }

    public function addCreditCardToForm($ccInfo)
    {

        $this->addContentToFormField(
            WebDriverBy::id("cc_first_name"),
            "TEST-".$ccInfo->first_name
        );
        $this->addContentToFormField(
            WebDriverBy::id("cc_last_name"),
            "TEST-".$ccInfo->last_name
        );
        $this->addContentToFormField(
            WebDriverBy::id("cc_number"),
            $ccInfo->card_number
        );
        $this->addContentToFormField(
            WebDriverBy::id("cc_cvv"),
            $ccInfo->card_cvv
        );

    }

}
