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
     * TODO: Pass "WebDriverBy" not Selector string
     */
    public function clickButton(WebDriverBy $by)
    {
        try {
            print("ACTION   : Looking for button".PHP_EOL);
            $btn = $this->_driver->findElement($by);
            print("ACTION   : Found button".PHP_EOL);
            $btn->getLocationOnScreenOnceScrolledIntoView();
            print("ACTION   : Scrolled to button".PHP_EOL);
            print("ACTION   : Waiting for button to be clickable".PHP_EOL);
            $this->_driver->wait(20, 250)->until(
                WebDriverExpectedCondition::elementToBeClickable($by)
            );
            print("ACTION   : Button is now clickable".PHP_EOL);
            $btn->click();
            print("ACTION   : Clicked button".PHP_EOL);
        } catch (NoSuchElementException $e) {
            // Should we make sure page has not changed?
            // print("EXCEPTION: NoSuchElementException [Button could not be found]".PHP_EOL);
            // throw new NoSuchElementException("Button was not found", 1);
            return false;
        } catch (UnknownServerException $e) {
            // print("EXCEPTION: UnknownServerException [Button could not be clicked]".PHP_EOL);
            // throw new AutomationException("Element is not clickable");
            return false;
        }

    }

    /**
     * [enterFieldData description]
     * @param  WebDriverBy $by    [description]
     * @param  [type]      $value [description]
     * @return [type]             [description]
     */
    public function enterFieldData(WebDriverBy $by, $value)
    {
        print("VALUE    : $value".PHP_EOL);

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
     * @param WebDriverBy $by    [description]
     * @param [type]      $value [description]
     */
    public function setSelectValue(WebDriverBy $by, $value)
    {
        print("VALUE    : $value".PHP_EOL);
        $element = $this->_driver->findElement($by);
        $element->getLocationOnScreenOnceScrolledIntoView();

        $this->_driver->wait(20, 250)->until(
            WebDriverExpectedCondition::elementToBeClickable($by)
        );

        $select = new WebDriverSelect($element);

        // Get Current Value
        // If Current Value == Value, then return
        $cval = $select->getFirstSelectedOption();
        if($cval != $value) {
          $select->selectByVisibleText($value);
        }

    }

    /**
     * [getOptions description]
     * @param  WebDriverBy $by [description]
     * @return [type]          [description]
     */
    public function getOptions(WebDriverBy $by)
    {
        $select = $this->_driver->findElement($by);

        $wds = new WebDriverSelect($select);
        return $wds->getOptions();
    }

    /**
     * [getHiddenOptions description]
     * @param  [type] $elementId [description]
     * @return [type]            [description]
     */
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
     * [getPageDataPoints description]
     * @return [type] [description]
     */
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

    /**
     * [logConsoleMessages description]
     * @return [type] [description]
     */
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

        $this->_driver->wait(20, 250)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector($cssClass)
            )
        );
    }

    /**
     * [refreshPage description]
     * @return [type] [description]
     */
    public function refreshPage()
    {
        $this->_driver->navigate()->refresh();
    }

    /**
     * [getSaveNotificationResult description]
     * @return [type] [description]
     */
    public function getSaveNotificationResult()
    {

    }

    /**
     * [changeFieldFocus description]
     * @return [type] [description]
     */
    public function changeFieldFocus()
    {
        $kb = $this->_driver->getKeyboard();
        $kb->sendKeys("\xEE\x80\x8C");
    }

    /**
     * [setCheckBoxCheckedState description]
     * @param WebDriverBy $by    [description]
     * @param [type]      $state [description]
     */
    public function setCheckBoxCheckedState(WebDriverBy $by, $state)
    {
      $el = $this->_driver->findElement($by);

      if($el->isSelected() !== $state) {
        $lel = $this->_driver->findElement(
          WebDriverBy::cssSelector( sprintf('label[for=%s]', $el->getAttribute("id")) )
        );

        $lel->click();
      }
    }
}
