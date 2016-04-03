<?php
namespace HBI;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver;

/**
*
*/
class HBIWebUI
{
    private $_driver;

    function __construct($driver)
    {
        $this->_driver     = $driver;
    }

    public function clickButton($selector)
    {
        $btn = $this->_driver->findElement(
          WebDriverBy::cssSelector($selector)
        );
        $btn->click();
    }

    public function enterFieldData($fieldname, $fieldvalue, $fieldby)
    {
        $field = $this->_driver->findElement(
          WebDriverBy::$fieldby($fieldname)
        );
        $field->sendKeys($fieldvalue);
    }

    public function clickTab($tabtext)
    {
        $lt = $this->_driver->findElement(
            WebDriverBy::linkText($tabtext)
        );
        $lt->click();
    }

    public function setSelectValue()
    {
        // $select = $this->_driver->findElement(
        //     WebDriverBy::
        // );
    }

    public function clearField($fieldname, $fieldby)
    {
        $element = $this->_driver->findElement(
          WebDriverBy::$fieldby($fieldname)
        );
        $element->clear();
    }

    public function removeInputedValue($fieldname, $fieldby)
    {
        $elements = $this->_driver->findElements(
          WebDriverBy::$fieldby($fieldname)
        );

        $elcnt = count($elements);

        if( $elcnt > 0 ) {
            $el = $this->_driver->findElement(
                WebDriverBy::$fieldby($fieldname)
            );
            $el->click();
        }
    }
}
