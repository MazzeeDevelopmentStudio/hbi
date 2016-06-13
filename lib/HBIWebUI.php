<?php
namespace HBI;

use HBI\Exception\AutomationException;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\RemoteKeyboard;
use \Facebook\WebDriver\Remote\RemoteExecuteMethod;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverSelect;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver;

use \Facebook\WebDriver\Exception\UnknownServerException;
use \Facebook\WebDriver\Exception\NoSuchElementException;

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
        try {
            $btn->click();
        } catch (UnknownServerException $e) {
            // throw new AutomationException("Element is not clickable");
        }

    }

    /**
     * DEPRECIATED
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

        $field->clear();
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
    public function setSelectValue(WebDriverBy $by, $value)
    {
        // print_r($by);

        $element = $this->_driver->findElement($by);
        $element->getLocationOnScreenOnceScrolledIntoView();

        $select = new WebDriverSelect($element);

        // Get Current Value
        // If Current Value == Value, then return
        $cval = $select->getFirstSelectedOption();
        if($cval != $value) {
          $select->selectByVisibleText($value);
        }

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

    public function getPageDataPoints()
    {
        $script  = 'return {
            funnelId:window.funnel_id,
            pageId:window.funnel_page_id,
            pageName:window.funnel_page_name,
            stageId:window.funnel_stage_id,
            stageType:window.funnel_stage_name};
        ';
        $results = $this->_driver->executeScript($script);

        return (object)$results;
    }

    public function logConsoleMessages()
    {
        $script = "
                    window.collectedErrors = [];
                    window.onerror = function(errorMessage) {
                        window.collectedErrors[window.collectedErrors.length] = errorMessage
                    };";
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
        $el       = isset($elements[$rnd]) ? $elements[$rnd] : false;

        // quirk in selenium.... in case there is only one element
        // if(!$el) {
        //     $el = $this->_driver->findElement($by);
        // }

        return $el;
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

    public function changeFieldFocus()
    {
        // $rem = new RemoteExecuteMethod($this->_driver);
        // $kb = new RemoteKeyboard($rem);

        $kb = $this->_driver->getKeyboard();
        $kb->sendKeys("\xEE\x80\x8C");

    }
}
