<?php
namespace HBI;

use HBI\Exception\AutomationException;
use HBI\HBIBrowserList;

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
class HBIBrowser
{
    private $_capabilities;
    private $_driver;
    private $_window;
    private $_panel;
    private $_webui;
    public  $browserList;

    private $_browsers;

    function __construct($hub, $browser = "firefox")
    {
        if($browser == 'random') {
            $browser = $this->setRandomBrowser();
        }

        $this->_capabilities = DesiredCapabilities::$browser();
        $this->_driver       = RemoteWebDriver::create($hub, $this->_capabilities, 5000);
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

    public function getCapabilities()
    {
        $cap = (array)$this->_capabilities;
        return array_values($cap)[0];
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

    /**
     * [setRandomBrowser description]
     */
    private function setRandomBrowser()
    {
        $p   = $GLOBALS['platfrm'] ? $GLOBALS['platfrm'] : array_rand(SELENIUMHUB);
        $pbl = PLATFORMBROWSERLIST;

        return array_rand($pbl[$p]);
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
            // print_r(debug_backtrace());
            return;
            throw new AutomationException("Element Not Found");
        }

        // Bring it to view
        $el->getLocationOnScreenOnceScrolledIntoView();

        $this->_driver->wait(5, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($el)
        );

        try {
            // click it
            $el->click();
        // } catch (UnknownServerException $e) {
        } finally {
            // Element is not clickable
            // throw new AutomationException("Element is not clickable");
        }

    }

    public function waitForElementToBeClickable(WebDriverBy $by)
    {
        $this->_driver->wait(5, 10)->until(
            WebDriverExpectedCondition::elementToBeClickable($by)
        );
    }

    public function waitForElement(WebDriverBy $by)
    {
        $this->_driver->wait(5, 1000)->until(
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

    // public function addCreditCardToForm($ccInfo)
    // {

    //     $this->addContentToFormField(
    //         WebDriverBy::id("cc_first_name"),
    //         "TEST-".$ccInfo->first_name
    //     );
    //     $this->addContentToFormField(
    //         WebDriverBy::id("cc_last_name"),
    //         "TEST-".$ccInfo->last_name
    //     );
    //     $this->addContentToFormField(
    //         WebDriverBy::id("cc_number"),
    //         $ccInfo->card_number
    //     );
    //     $this->addContentToFormField(
    //         WebDriverBy::id("cc_cvv"),
    //         $ccInfo->card_cvv
    //     );

    // }

    public function TakeScreenshot($element=null)
    {
        // Change the Path to your own settings
        $screenshot = $this->TempDirectoryPath . time() . ".png";

        // Change the driver instance
        $this->driver->takeScreenshot($screenshot);
        if(!file_exists($screenshot)) {
            throw new Exception('Could not save screenshot');
        }

        if( ! (bool) $element) {
            return $screenshot;
        }

        $element_screenshot = $this->TempDirectoryPath . time() . ".png"; // Change the path here as well

        $element_width = $element->getSize()->getWidth();
        $element_height = $element->getSize()->getHeight();

        $element_src_x = $element->getLocation()->getX();
        $element_src_y = $element->getLocation()->getY();

        // Create image instances
        $src = imagecreatefrompng($screenshot);
        $dest = imagecreatetruecolor($element_width, $element_height);

        // Copy
        imagecopy($dest, $src, 0, 0, $element_src_x, $element_src_y, $element_width, $element_height);

        imagepng($dest, $element_screenshot);

        // unlink($screenshot); // unlink function might be restricted in mac os x.

        if( ! file_exists($element_screenshot)) {
            throw new Exception('Could not save element screenshot');
        }

        return $element_screenshot;
    }

    public function chromeScreenshot()
    {
        // # needs the selenium-webdriver and mini_magick gems

        // # Substitute these with the size of your web page
        $page_width = 2000;
        $page_height = 4000;

        $height_captured = 0;
        $y_tiles = 0;

        // driver = Selenium::WebDriver # Connect to your Selenium Webdriver
        // driver.navigate.to iframe_url
        // driver.manage.window.maximize

        // while height_captured < page_height
        //   width_captured = 0
        //   x_tiles = 0

        //   while width_captured < page_width
        //     tile_file = "screenshot-#{y_tiles}-#{x_tiles}.png"

        //     driver.execute_script "window.scrollTo(#{width_captured}px, #{height_captured});"

        //     driver.save_screenshot tile_file

        //     screenshot = MiniMagick::Image.new tile_file
        //     width_captured += screenshot.width
        //     x_tiles += 1
        //   end

        //   height_captured += screenshot.height
        //   y_tiles += 1
        // end

        // # Use imagemagick's montage command to stitch the screenshot tiles together
        // `montage screenshot-[0-#(y_tiles}]-[0-#{x_tiles}].png -tile #{x_tiles}x#{y_tiles} -geometry #{screenshot.width}x#{screenshot.height}+0+0 screenshot.png`
    }


}
