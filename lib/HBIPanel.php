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
        $this->_driver->get(QASERVER.'/login');

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

        $this->_webui->clickButton('button.btn.btn-success.btn-block');
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

    public function clickNavigationItem($selector)
    {
        $this->_webui->clickButton($selector);
    }

    public function clickSubNavigationItem($parent, $text)
    {
        $el  = WebDriverBy::linkText($text);

        // We need to first check to see if the parent is already open
        // But until we get the rendered code fixed, we will just click
        // the dashboard view each time first.
        $this->clickNavigationItem(".fa.fa-home");
        // $element = $this->_driver->findElement($el);
        // if ($element->isDisplayed()) {
            $this->clickNavigationItem($parent);
        // }

        // This is one area where we need to change how
        // we do the interfaces for the panel
        $btn = $this->_driver->findElement(
          $el
        );
        $btn->click();
    }

    public function clickButtonElement($selector)
    {
        $this->_webui->clickButton($selector);
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
            $this->_driver->wait(20, 1000)->until(
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
}
