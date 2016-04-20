<?php
namespace HBI;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverSelect;
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

    /**
     * [clickButton description]
     * @param  [type] $selector [description]
     * @return [type]           [description]
     */
    public function clickButton($selector)
    {
        $btn = $this->_driver->findElement(
          WebDriverBy::cssSelector($selector)
        );
        $btn->click();
    }

    /**
     * [enterFieldData description]
     * @param  [type] $fieldname  [description]
     * @param  [type] $fieldvalue [description]
     * @param  [type] $fieldby    [description]
     * @return [type]             [description]
     */
    public function enterFieldData($fieldname, $fieldvalue, $fieldby)
    {
        error_log(print_r($fieldvalue,true).PHP_EOL);

        $field = $this->_driver->findElement(
          WebDriverBy::$fieldby($fieldname)
        );
        $field->sendKeys($fieldvalue);
    }

    /**
     * [clickTab description]
     * @param  [type] $tabtext [description]
     * @return [type]          [description]
     */
    public function clickTab($tabtext)
    {
        $lt = $this->_driver->findElement(
            WebDriverBy::linkText($tabtext)
        );
        $lt->click();
    }

    /**
     * [setSelectValue description]
     */
    public function setSelectValue()
    {
        // $select = $this->_driver->findElement(
        //     WebDriverBy::
        // );
    }

    public function getOptions($selector)
    {
        $select = $this->_driver->findElement(
          WebDriverBy::cssSelector($selector)
        );

        $wds = new WebDriverSelect($select);
        return $wds->getOptions();
    }

    /**
     * [clearField description]
     * @param  [type] $fieldname [description]
     * @param  [type] $fieldby   [description]
     * @return [type]            [description]
     */
    public function clearField($fieldname, $fieldby)
    {
        $element = $this->_driver->findElement(
          WebDriverBy::$fieldby($fieldname)
        );
        $element->clear();
    }

    /**
     * [removeInputedValue description]
     * @param  [type] $fieldname [description]
     * @param  [type] $fieldby   [description]
     * @return [type]            [description]
     */
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

    /**
     * [waitForDataTableLoad description]
     * @return [type] [description]
     */
    public function waitForDataTableLoad()
    {
        $cssClass = "div.dataTables_paginate.paging_simple_numbers";

        $this->_driver->wait(20, 1000)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector($cssClass)
            )
        );
    }

    public function refreshPage()
    {
        $this->_driver->navigate()->refresh();
    }

    public function getSaveNotificationResult()
    {

    }
}
