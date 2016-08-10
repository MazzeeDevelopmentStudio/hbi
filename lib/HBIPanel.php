<?php
namespace HBI;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver;

/**
 *
 */
class HBIPanel
{

    private $_driver;
    private $_window;
    private $_cookies;
    private $_webui;
    private $_successClass;

    function __construct(RemoteWebDriver $rwd)
    {
        $this->_driver = $rwd;

        $this->_driver->manage()->timeouts()->implicitlyWait(10);

        $this->_window = New WebDriverWindow($this->_driver);
        $this->_webui  = new HBIWebUI($this->_driver);

        $this->successClass = "gritter-item-wrapper growl-info";
    }

    Public function logIn(Array $credentials)
    {
        $this->AddCookie(array(
          'name' => 'cookie_name',
          'value' => 'cookie_value',
        ));

        $this->_webui->enterFieldData(
            WebDriverBy::name("email"),
            $credentials['user']
        );
        $this->_webui->enterFieldData(
            WebDriverBy::name("password"),
            $credentials['pass']
        );

        $this->_webui->clickButton(
            WebDriverBy::cssSelector('button.btn.btn-success.btn-block')
        );
    }

    Public function logOut()
    {

        $btn = $this->_driver->findElement(
            WebDriverBy::cssSelector('.btn.btn-default.dropdown-toggle')
        );
        $btn->click();

        $link = $this->_driver->findElement(
            WebDriverBy::cssSelector('.glyphicon.glyphicon-log-out')
        );
        $link->click();

    }

    public function waitForLoad()
    {
        $this->_driver->wait(10)->until(
          WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
            WebDriverBy::className('leftpanel')
          )
        );
    }

    public function isDashboardPanel()
    {
        $title = $this->_driver->getTitle();
        if(stripos($title, 'Dashboard')===FALSE) {
            return FALSE;
        }

        return TRUE;
    }

    private function addCookie(Array $attributes)
    {
        // adding cookie
        $this->_driver->manage()->deleteAllCookies();
        $this->_driver->manage()->addCookie($attributes);

        $this->_cookies = $this->_driver->manage()->getCookies();
    }

    public function clickNavigationItem(WebDriverBy $by)
    {
        $this->_webui->clickButton($by);
    }

    public function clickSubNavigationItem($parentBy, $textBy)
    {
        // We need to first check to see if the parent is already open
        // But until we get the rendered code fixed, we will just click
        // the dashboard view each time first.
        $this->clickNavigationItem(
            WebDriverBy::cssSelector(".fa.fa-home")
        );

        // $element = $this->_driver->findElement($el);
        // if ($element->isDisplayed()) {
        $this->clickNavigationItem(
            // $parentBy
            WebDriverBy::cssSelector($parentBy)
        );
        // }

        // This is one area where we need to change how
        // we do the interfaces for the panel
        $this->_driver
                 ->findElement(
                    // $textBy
                    WebDriverBy::linkText($textBy)
                )
                 ->click();
    }

    public function clickButtonElement(WebDriverBy $by)
    {
        $this->_webui->clickButton($by);
    }

    public function waitForNotification()
    {
        $messageBx    = "#gritter-notice-wrapper .gritter-item-wrapper";
        $errorClass   = "gritter-item-wrapper growl-danger";
        $successClass = "gritter-item-wrapper growl-info";
        $txResult     = false;

        // Message TITLE: div.gritter-item div.gritter-with-image span.gritter-title
        // Message TEXT: div.gritter-item div.gritter-with-image p

        try {
            $this->_driver->wait(20, 250)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector($messageBx)
                )
            );

            $msgBx = $this->_driver->findElement(
                WebDriverBy::cssSelector($messageBx)
            );

            $msgContent = $this->getNotificationBoxContent($msgBx);

            if(!$txResult) {
                // error_log( sprintf( 'Results Message: (%s) %s', $msgTitle->getText(), $msgBody->getText() ) );
                error_log( sprintf( 'Results Message: (%s) %s', $msgContent['Title']->getText(), $msgContent['Body']->getText() ) );
            }

        } catch (TimeOutException $e) {
            // Should we capture this?
            error_log('THIS CAPTURE WORKS!'.PHP_EOL);
            error_log( print_r($e, true) );
        }

        return  $txResult;
    }

    /**
     * [getNotificationBoxContent description]
     * @param  WebDriverElement $element [description]
     * @return [type]                    [description]
     */
    public function getNotificationBoxContent(WebDriverElement $element)
    {

        $msgBxClass = $element->getAttribute('class');
        $txResult = $msgBxClass == $this->successClass ? true : false;

        // We are actually not doign the right thing here
        // TODO: Fix this
        $msgTitle = $this->_driver->findElement(
            WebDriverBy::cssSelector('div.gritter-item div.gritter-with-image span.gritter-title')
        );

        // We are actually not doign the right thing here
        // TODO: Fix this
        $msgBody = $this->_driver->findElement(
            WebDriverBy::cssSelector('div.gritter-item div.gritter-with-image p')
        );

        return array("Title"=>$msgTitle, "Body"=>$msgBody);

    }

    public function setTableViewSize($size)
    {
        // Select Name: tOrders_length
        $this->_webui->setSelectValue(
            WebDriverBy::cssSelector('select[name=tOrders_length]'),
            "$size"
        );

    }

    public function getAllOrderTableData()
    {
        // Table ID: tOrders

        // Order ID
        // Created
        // Total
        // Buyer
        // Status
    }

    public function getCurrentOrderTableData()
    {
        // Table ID: tOrders
        $elements = $this->_driver->findElements(
            WebDriverBy::cssSelector('table#tOrders tbody tr td')
        );

        $i       = 0;
        $tmp     = array();
        $data    = array();
        $tblCols = array('Order ID','Created', 'Total', 'Buyer', 'Status');

        foreach ($elements as $el) {
            $tmp[ $tblCols[$i] ] = $el->getText();

            switch ($tblCols[$i]) {
                case 'Order ID':
                    $tmp['oURL'] = $this->getHREFOfChildElement($el);
                    break;
                case 'Buyer':
                    $tmp['bURL'] = $this->getHREFOfChildElement($el);
                    break;                
            }

            if( $i++ >= count($tblCols)-1 ) {
                $data[] = $tmp;
                $tmp = array();
                $i=0;
            }
        }

        // print_r($data);

        return $data;
    }

    public function getHREFOfChildElement(WebDriverElement $el)
    {
            $cel = $el->findElement(
                WebDriverBy::xpath(".//*")
            );

            return $cel->getAttribute('href');
    }


    public function clickNextPaginationSetButton()
    {
        return $this->clickPaginationButton('tOrders_next');
    }

    public function clickPreviousPaginationSetButton()
    {
        return $this->clickPaginationButton('tOrders_previous');
    }

    public function clickPaginationButton($btnId)
    {
        return $this->_webui->clickButton(
            WebDriverBy::id($btnId)
        );
        
        $this->waitForTableProcessingToComplete();
    }

    public function clickPaginationPageNumber($number)
    {

    }

    public function getPaginationPageNumbers()
    {
        // paginate_button 
        // div#tOrders_paginate span a.paginate_button
    }

    public function getCurrentPaginationPageNumber()
    {
        // div#tOrders_paginate span a.paginate_button.current
    }

    public function jumpToPaginationPage($pageNumber)
    {
        // print( sprintf('Jumping to Page Number: %s'.PHP_EOL, $pageNumber) );

        $i = 1;
        while ($i <= $pageNumber) {
            $this->clickNextPaginationSetButton();
            $i++;
        }

        $this->waitForTableProcessingToComplete();

    }

    public function getPagitationPageCount()
    {
        // div#tOrders_paginate span a.paginate_button
        $elements = $this->_driver->findElements(
            WebDriverBy::cssSelector('div#tOrders_paginate span a.paginate_button')
        ); 

        $el = $elements[ count($elements)-1 ];

        return $el->getText();
    }

    public function waitForTableProcessingToComplete()
    {
        $this->_driver->wait(15, 250)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('processingmodal')
            )
        );

    }


    public function isOrderURIValid($order)
    {

        $this->_driver->get( sprintf( '%s/dashboard/orders/%s', CORESERVER[ ENVIRONMENT ], $order->guid ));

        // div.pageheader h2
        $el = $this->_driver->findElement(
            WebDriverBy::cssSelector('div.pageheader h2')
        );

        $orderId = str_replace('Order #', NULL, $el->getText());

        return $order->id != $orderId ? FALSE : TRUE;
    }

    public function setSortOrderByDateDesc()
    {
        $el = NULL;
        $elements = $this->_driver->findElements(
            WebDriverBy::cssSelector('div.tOrders thead tr th')
        );

        foreach ($elements as $element) {
            print_r($element);
            if($element->getText() == "Created") {
                $el = $element;
                break;
            }
        }

        if($el && $el->getAttribute('class') == 'sorting_desc') {

            $el->click();
        }

        return true;
    }

}
