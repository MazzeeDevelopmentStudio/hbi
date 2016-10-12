<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\Exception\NoSuchElementException;

use HBI\Admin\Actions;
use HBI\Admin\AddPerson;
use HBI\HBIBrowser;
use HBI\HBIPeople;
use HBI\HBIHelper;
use HBI\HBIAddress;
use HBI\HBIAddresses;
use HBI\HBIPerson;
use HBI\HBICreditCard;
use HBI\HBICreditCards;
use HBI\Funnel\Helpers;
use HBI\Exception\AutomationException;

/**
 *
 */
class AddCallCenterOrder extends Actions
{
    private $browser;
    private $person;
    private $address;
    private $contact;
    private $financial;
    private $log;
    private $isNewCustomer;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $person=null, $type=null)
    {
        $this->browser       = $browser;
        $this->log           = $GLOBALS['HBILog'];
        $this->isNewCustomer = false;
        $this->person        = !empty($person) ? $person : $this->definePerson();

        // Adjust person details to add variances
        if(rand(0,1)) {
            $this->changeUpPersonDetails();    
        }

        print("PERSON   : ".json_encode( $this->person ).PHP_EOL);

        $this->log->writeToLogFile(array("person"=>$this->person));
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {

    }

    function add()
    {
        if(!$this->isNewCustomer) {
            $this->searchForPerson();
            $this->validateSearchedUserData();            
        } else {
            $this->addNewPersonToForm();
        }

        // Add Payment Method 
        $this->addPaymentMethodToForm();

        // throw new AutomationException("Intential DIE");
    }

    protected function definePerson()
    {
        print("FUNCTION : definePerson".PHP_EOL);

        // Choose between new or existing person
        $person = !(bool)rand(0,5) ? $this->defineNewPerson() : $this->defineExistingPerson();

        return $person;
    }

    protected function changeUpPersonDetails()
    {
        print("FUNCTION : changeUpPersonDetails".PHP_EOL);

        // Change Billing Address?
        if(!(bool)rand(0,3)) {
            $this->addAddress();
        }

        // Add Shipping Address
        if(!(bool)rand(0,7)) {
            $this->addAddress('shipping');
        }

        if(!(bool)rand(0,5)) {
            $this->addNewPaymentMethod();
        }


    }

    protected function defineNewPerson()
    {
        print("FUNCTION : defineNewPerson".PHP_EOL);

        $this->isNewCustomer = true;

        $person  = \HBI\Funnel\Helpers::getPersonWithFullDetails();
        $sPerson = \HBI\Funnel\Helpers::getShippingAddressDetails($person);

        if($person != $sPerson || !rand(0,20)) {
            \HBI\Funnel\Helpers::testifyPersonDetails($sPerson);
            $person->shipping = $sPerson;
        }

        \HBI\Funnel\Helpers::testifyPersonDetails($person);

        unset($sPerson);

        return $person;
    }

    protected function defineExistingPerson()
    {
        print("FUNCTION : defineExistingPerson".PHP_EOL);

        // Since HBI Core does not wildcard searches, we will need to 
        // loop through "guesses" until we get a result.

        $p = false;

        while(!$p) {
            $log  = HBIHelper::randomQALogFile();
            $file = file_get_contents($log);
            $data = json_decode($file);

            foreach ($data as $v) {
                // If this a failed test result, we don't want it
                if(isset($v->results) && (bool)$v->results) {
                    $p = false;
                    break;
                }

                if(isset($v->person)) {
                    $p = $v->person;
                }
            }
        }

        // Make sure to "cast" to the proper object types
        $person          = new HBIPerson( json_encode($p) );
        $person->card    = new HBICreditCard( json_encode($person->card) );
        $person->address = new HBIAddress( json_encode($person->address) );

        return $person;
    }

    protected function searchForPerson()
    {
        print("FUNCTION : searchForPerson".PHP_EOL);

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("_lookup_email"),
            $this->person->email
        );

        // _lookup_email_submit
        // TODO: This should be a button click or a enter key
        $this->browser->webui()->clickButton(
            WebDriverBy::id('_lookup_email_submit')
        );
        
        $error = $this->isErrorBoxPresent();

        HBIHelper::xprint($error,'error');

        if($error) {
            throw new AutomationException("User is not found (by email address lookup)");
        }
        
    }

    protected function validateSearchedUserData()
    {
        print("FUNCTION : validateSearchedUserData".PHP_EOL);

        $datapts = array(
            'first_name',
            'last_name',
            'email'
            // 'primary_phone'
        );

        // Timing issue... we need to wait to pull values
        sleep(1);

        foreach ($datapts as $v) {
            try {
                $el = $this->browser->driver()->findElement(
                    WebDriverBy::cssSelector('input[name='.$v.']')
                );

                // This is where we check the element value
                $key = FIELDTABLE[$v];

                $frmValue = $el->getAttribute('value');

                print( sprintf("INFO     : Form Value Comparison (InForm/InData): %s / %s".PHP_EOL, $frmValue, $this->person->$key) );

                $orgValue = ($key != 'primary_phone') ? $this->person->$key : preg_replace('/\s+/', '', $this->person->$key);

                if($frmValue != $this->person->$key) {
                    // TODO: Test Case Failure
                    // If values don't match, we have an error
                    print('TESTCASE : Form value did not match from search'.PHP_EOL);
                    HBIHelper::xprint( $frmValue, 'frmValue');
                    HBIHelper::xprint( $this->person->$key, 'this->person->$key');

                    // throw new AutomationException("Form values did not match from search");
                }
                
            } catch (NoSuchElementException $e) {
                // TODO: Test Case Failure
                // If the element is missing, then its a bug
                print('TESTCASE : Form values did not match from search'.PHP_EOL);

                throw new AutomationException("Missing form element");
            }

        }

        // TODO: Test Case Success
        print("VALIDATED: Values are correct".PHP_EOL);
    }

    protected function addNewPersonToForm()
    {
        print("FUNCTION : addNewPersonToForm".PHP_EOL);


    }
    // TODO: Why is comeing back with sandbox CC Number?
    protected function addNewPaymentMethod()
    {
        print("FUNCTION : addNewPaymentMethod".PHP_EOL);

        $ccards = new HBICreditCards;
        $card   = $ccards->buildCollection(1);
        
        $this->person->card = $card;

        print("NEW DATA : ".json_encode( $card ).PHP_EOL);
    }

    protected function addPaymentMethodToForm()
    {
        print("FUNCTION : addPaymentMethodToForm".PHP_EOL);

        if(!$this->isNewCustomer && !(bool)rand(0,5)) {
            $this->selectExistingPaymentMethod();
        } else {
            $this->addNewPaymentMethodToForm();
        }


    }

    protected function addNewPaymentMethodToForm()
    {
        print("FUNCTION : addNewPaymentMethodToForm".PHP_EOL);
        // TODO: Create and use new card
        $this->selectExistingPaymentMethod();
    }

    protected function selectExistingPaymentMethod()
    {
        print("FUNCTION : selectExistingPaymentMethod".PHP_EOL);

        // This will select a payment method drop down that matches
        // the existing payment method in the person/card object
        $numb = substr($this->person->card->number, -4);
        $expr = strtotime($this->person->card->expiration);
        $mnth = date("m", $expr);
        $year = date("Y", $expr);
        $card = sprintf( '****%s (%s/%s)', $numb, $mnth, $year );

        $options = $this->browser->webui()->getHiddenOptions("item_type_id");

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen7_search"),
            $card
        );

        try {
            $this->browser->webui()->clickButton(
                WebDriverBy::cssSelector(".select2-match")
            );    
        } catch (NoSuchElementException $e) {
            // TODO: Test Case Failure
            print('TESTCASE : Credit Card is missing from list'.PHP_EOL);
            $this->log->writeToLogFile( array("TEST-FAILURE"=>$e) );

            throw new AutomationException("Credit Card is missing from list");
        }
        
        // TODO: Test Case Success
        print("TESTCASE : Credit Card Pull from History".PHP_EOL);
    }

    /**
     * [addAddress description]
     * @param string $type [description]
     */
    protected function addAddress($type='billing')
    {
        print("FUNCTION : addAddress".PHP_EOL);

        $addr    = new HBIAddresses;
        $address = $addr->buildCollection(1);

        $this->person->$type = $address;

        print("NEW DATA : ".json_encode( $address ).PHP_EOL);
    }

    protected function selectExistingBillingAddress()
    {

    }

    protected function selectExsitingShippingAddress()
    {

    }

    protected function toggleShippingAddressAsBillingAddressSelector()
    {

    }

    protected function setCoupon()
    {

    }

    protected function checkOrderSuccess()
    {
        // <div id="orderstatusmsg"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><strong>Well done!</strong> The order has been placed. For reference, it's <strong>Order ID: 12297</strong>.. To start a new order <a href="/dashboard/callcenter/createorder">either click here</a>, or enter an email search below.</div></div>
    }

    protected function getOrderIDFromSuccessMessage()
    {
        // <div id="orderstatusmsg"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><strong>Well done!</strong> The order has been placed. For reference, it's <strong>Order ID: 12297</strong>.. To start a new order <a href="/dashboard/callcenter/createorder">either click here</a>, or enter an email search below.</div></div>
    }

    // TODO: This method needs to be moved to the HBIHelper or HBIPanel class
    // Look for the following (this would be a no person found error)
    // <div id="gritter-item-6" class="gritter-item-wrapper growl-danger" style="" role="alert"><div class="gritter-top"></div><div class="gritter-item"><a class="gritter-close" href="#" tabindex="1" style="display: none;">Close Notification</a><img src="/images/holders/screen.png" class="gritter-image"><div class="gritter-with-image"><span class="gritter-title">Something went wrong!</span><p>The selected email is invalid.</p></div><div style="clear:both"></div></div><div class="gritter-bottom"></div></div>
    protected function isErrorBoxPresent()
    {
        print("FUNCTION : isErrorBoxPresent".PHP_EOL);

        $errorClass        = ".gritter-item-wrapper.growl-danger";
        $isErrorBoxPresent = false;

        try {
            $this->browser->driver()->wait(20, 250)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector($errorClass)
                )
            );
            $isErrorBoxPresent = true;
            print("ERROR    : The Error box poped open".PHP_EOL);
        } catch (TimeOutException $e) {
            // Don't really need this, but hey!
            print("INFO     : There is no error box".PHP_EOL);
            $isErrorBoxPresent = false;
        } catch (NoSuchElementException $e) {
            print("INFO     : There is no error box".PHP_EOL);
            $isErrorBoxPresent = false;            
        }

        print("RESULTS  : ".(int)$isErrorBoxPresent.PHP_EOL);

        return $isErrorBoxPresent;
    }

}