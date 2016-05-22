<?php
namespace HBI;

use HBI\Exception\AutomationException;

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
    public function enterFieldData2($fieldname, $fieldvalue, $fieldby)
    {
        $field = $this->_driver->findElement(
          WebDriverBy::$fieldby($fieldname)
        );
        $field->sendKeys($fieldvalue);
    }

    public function enterFieldData(WebDriverBy $by, $value)
    {
        $field = $this->_driver->findElement($by);

        $field->sendKeys($value);
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

    public function getOptions(WebDriverBy $by)
    {
        $select = $this->_driver->findElement($by);

        $wds = new WebDriverSelect($select);
        return $wds->getOptions();
    }

    public function getHiddenOptions($elementId)
    {   $script = "
            var texts = [];
            var sel = document.getElementById('$elementId');
            for (var i=0, n=sel.options.length;i<n;i++) {
              if (sel.options[i].text && sel.options[i].disabled == false) {
                texts.push(sel.options[i].text);
              }
            }

            return texts;";

        $results = $this->_driver->executeScript($script);

        return $results;
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
     * [getOneOfManyElements description]
     * @param  WebDriverBy $webdriverby [description]
     * @return [type]                   [description]
     */
    public function getOneOfManyElements(WebDriverBy $by) {
        $elements = $this->_driver->findElements($by);
        $rnd      = rand(0, count($elements)-1);

        return $elements[$rnd];
    }

    /**
     * [waitForDataTableLoad description]
     * @return [type] [description]
     */
    public function waitForDataTableLoad()
    {
        // $cssClass = "div.dataTables_paginate.paging_simple_numbers";
        $cssClass = "table.dataTable tbody tr.odd";

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
