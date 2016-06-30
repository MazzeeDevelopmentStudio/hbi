<?php
namespace HBI;

use HBI\HBIHelper;
use HBI\Funnel\PgDataObject;
use HBI\Funnel\Helpers;

use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\Exception\NoSuchElementException;
use \Facebook\WebDriver\Exception\TimeOutException;

/**
*
*/
class HBIFunnelPage
{
    private $browser;
    private $hbilog;
    private $page;
    private $spf;
    private $opf;

    public  $funnelPage;


    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param HBILog     $hbilog  [description]
     */
    public function __construct(HBIBrowser $browser, HBILog $hbilog)
    {
        $this->browser = $browser;
        $this->hbilog  = $hbilog;

        $this->spf = array();
        $this->opf = array();

        $this->initiateNewPage();
    }

    /**
     * [initiateNewPage description]
     * @return [type] [description]
     */
    public function initiateNewPage()
    {
        print("FUNCTION : initiateNewPage".PHP_EOL);
        if($this->isSamePageObject()
            && ($this->page->pageId == $this->funnelPage->page->id)) {
            return;
        }

        $this->setPageDataObject();

        $pageId = $this->page->pageId;
        $json   = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-page',
                    array('page_id'=>$pageId)
                );

        $this->funnelPage = (object)json_decode($json);

        // $this->hbilog->writeToLogFile( array("FunnelPage"=> json_encode($this->funnelPage)) );
    }

    /**
     * [isSamePageObject description]
     * @return boolean [description]
     */
    private function isSamePageObject()
    {
        print("FUNCTION : isSamePageObject".PHP_EOL);

        $pg = $this->browser->webui()->getPageDataPoints();

        if(!isset($this->page->pageId)) {return false;}

        return $this->page->pageId == $pg->pageId ? true : false;
    }

    /**
     * [isNewPageObject description]
     * @return boolean [description]
     */
    private function isNewPageObject()
    {
        print("FUNCTION : isNewPageObject".PHP_EOL);

        $pg = $this->browser->webui()->getPageDataPoints();

        return $this->page->pageId == $pg->pageId ? false : true;
    }

    /**
     * [setPageDataObject description]
     */
    private function setPageDataObject()
    {
        print("FUNCTION : setPageDataObject".PHP_EOL);

        $pg         = json_encode($this->browser->webui()->getPageDataPoints());
        $this->page = new PgDataObject( $pg );

        // DEBUG OUTPUT
        print("PAGE     : ".json_encode($this->page).PHP_EOL);
        $this->hbilog->writeToLogFile(array("page"=>$this->page));

        unset($pg);
    }

    /**
     * [processPage description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     */
    public function processPage(HBIPerson $person)
    {
        print("FUNCTION : processPage".PHP_EOL);

        $ret      = false;

        if(SELF::isNewPageObject()) {
            SELF::initiateNewPage();
        }

        switch ($this->page->stageType) {
            case 'SalesPage':
                $ret = SELF::processSalesPageForm($person);
                break;

            case 'OrderForm':
                $ret = SELF::processOrderForm($person);
                break;

            case 'Upsell':
                SELF::processFunnelUpsell();
                $ret = true;
                break;

            case 'ThankYou':
                // TODO: Grab the values and make sure correct
                SELF::processFinalReceipt();
                $ret = false;
                break;

            case 'Downsell':
                SELF::processFunnelDownsell();
                $ret = true;
                break;

            case 'Presell':
                SELF::processFunnelPresell();
                $ret = false;
                break;

            default:
                $ret = false;
                break;
        }


        return $ret;
    }

    /**
     * [processSalesPageForm description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     */
    public function processSalesPageForm(HBIPerson $person)
    {
        print("FUNCTION : processSalesPageForm".PHP_EOL);

        $id = null;
        try {
            print("ACTION   : Getting Form ID".PHP_EOL);
            $el      = $this->browser->driver()->findElement(
                WebDriverBy::cssSelector("form")
            );
            $id      = $el->getAttribute('id');
            $formIds = array(
                'optinformmodal'=>'ajaxsubmit',
                'optinform'=>'ajaxsubmit'
            );
            $formType = isset($formIds[$id]) ? $formIds[$id] : 'checkoutsubmit';
        } catch (NoSuchElementException $e) {
            $formType = "checkoutsubmit";
        }
        print("SETTING  : FormType to '$formType'".PHP_EOL);

        // If we are a modal form or click to form, then we want to click
        // on the purchase button to invoke the form (optinform is already
        // visible on the page)
        if($id != 'optinform') {
            print("ACTION   : Sending Click Request for 'div.purchase-button'".PHP_EOL);
            try {
                $this->browser->webui()->clickButton(
                    WebDriverBy::cssSelector("div.purchase-button")
                );
            } catch (NoSuchElementException $e) {
                // The only valid reason for this is because
                // we already clicked it. So we make sure
                // Out page has not changed
                $this->initiateNewPage();
            }
        }

        // TODO: Wait for form to show
        // If the form is optinformodal, then we specificly get the modal ID
        // and see if its now visible
        if($id == 'optinformodal') {
            $el = $this->browser->driver()->findElement(
                WebDriverBy::cssSelector("div.purchase-button a.modal-toggle")
            );

            $mt  = str_replace("#", null, $mel->getAttribute("href"));

            // Check if form modal is now visible
            // $this->browser->waitForElement(
            //     WebDriverBy::id($mt)
            // );

            $formel = $this->browser->driver()->findElement(
              WebDriverBy::id($mt)
            );

            print("ACTION   : Waiting for Form ID $mt to be visible".PHP_EOL);
            $this->browser->driver()->wait(5, 250)->until(
                WebDriverExpectedCondition::visibilityOf($formel)
            );

        } elseif($formType == "checkoutsubmit") {
            // Wait for form to be useable
            print("ACTION   : Waiting for FormType $formType".PHP_EOL);
            $this->browser->waitForElement(
                WebDriverBy::cssSelector("form.".$formType)
            );
        }


        $ret = $this->processFunnelForm($person);

        $possbileButtons = array("submitcheckoutform","submit");

        // Which button do we have?
        $i       = 0;
        $located = null;
        while (!$located && $i<count($possbileButtons)) {
            print("ACTION   : Search for '$possbileButtons[$i]'".PHP_EOL);
            try {
                $located = $this->browser->driver()->findElement(
                    WebDriverBy::id($possbileButtons[$i])
                );
                print(sprintf("LOCATED  : %s".PHP_EOL, $located->getAttribute('id')));
            } catch (NoSuchElementException $e) {
                print("ALERT    : The button $possbileButtons[$i] was not found".PHP_EOL);
                $i++;
            }
        }

        if( $located ) {
            print("ACTION   : Sending Click Request for '$possbileButtons[$i]'".PHP_EOL);
            $this->browser->webui()->clickButton(
                WebDriverBy::id($possbileButtons[$i])
            );

        }

        // div#processingmodal
        // div.processingmodaltext
        try {
            $this->browser->driver()->wait(15, 250)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(
                    WebDriverBy::id('processingmodal')
                )
            );

        } catch (TimeOutException $e) {

        }

        return $ret;
    }

    /**
     * [processOrderForm description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     * TODO: Re-introduce shipping to 3rd person
     */
    public function processOrderForm(HBIPerson $person)
    {
        print("FUNCTION : processOrderForm".PHP_EOL);

        try {
            $this->browser->driver()->wait(5, 250)->until(
                WebDriverExpectedCondition::stalenessOf(
                    $this->browser->driver()->findElement(
                        WebDriverBy::id('processingmodal')
                    )
                )
            );
            print("ALERT    : Found Modal Windows at Function Start".PHP_EOL);

            return true;

        } catch (TimeOutException $e) {
            // TODO: Should we do anything?
        }

        // Wait for our Page Title
        $pgTitle = $this->page->pageName;

        $ret = $this->processFunnelForm($person);

        if(isset($person->shipping)) {
            try {
                $this->browser->webui()->setCheckBoxCheckedState(
                    WebDriverBy::id('shipping-sameas-billing'),
                    0
                );
                $this->processFunnelForm($person->shipping, 'shipping');
            } catch (NoSuchElementException $e) {
                throw new AutomationException("Form is missing shipping-sameas-billing");
            }
        }


        $addOnIds = $this->getAddonsIdsForPage();

        $addons = Funnel\Helpers::randomlySelectAddonsByIds($this->browser, $addOnIds);

        $this->hbilog->writeToLogFile(array("addons"=>$addOnIds));

        // DEBUG OUTPUT
        // TODO: Have this put out The addon details as well
        foreach ($addons as $addon) {
            print(sprintf("Addon    : %s".PHP_EOL,$addon));
        }

        $this->browser->driver()->takeScreenshot(
            sprintf('%s/%s/%s_ORDERFORM_VIEW.png',
            LOGSDIR, $GLOBALS['DATE'], $GLOBALS['SID'])
        );

        // Submit Order
        $this->browser->clickElement(
            WebDriverBy::id("submitcheckoutform")
        );

        try {
            $this->browser->driver()->wait(15, 250)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(
                    WebDriverBy::id('processingmodal')
                )
            );

        } catch (TimeOutException $e) {

        }


        return $ret;
    }

    /**
     * [processFunnelForm description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     * TODO: Check for Form Errors
     * TODO: Correct Form items & resubmit?
     */
    public function processFunnelForm(HBIPerson $person, $filter=null)
    {
        print("FUNCTION : processFunnelForm".PHP_EOL);

        // $this->page->stageType

        if($filter) {
            print("FILTER   : $filter".PHP_EOL);
        }

        $inputs = $this->browser->driver()->findElements(
            WebDriverBy::cssSelector('input[type="text"]')
        );

        $selects =  $this->browser->driver()->findElements(
            WebDriverBy::cssSelector('select')
        );

        $fields = array_merge($selects, $inputs);

        $reqfields = array();

        foreach ($fields as $field) {
            $fid    = $field->getAttribute("id");
            $b      = explode("-",$fid);
            $prefix = count($b) > 1 ? $b[0] : null;
            $root   = isset($b[1]) ? $b[1] : $b[0];
            $elType = $field->getTagName();
            $key    = $this->fieldKeyTranslationTable($root);
            $val    = array();

            if(!$field->isDisplayed()) {continue;}

            if($filter && ($prefix != $filter)) {continue;}

            // An array means either a complex and/ a child object
            // at this stage we will only have one sub-level support
            if(is_array($key)) {
                foreach ($key as $n2 => $n1) {
                    $val[] = $person->$n1->$n2;
                }
                $value = implode(" ", $val);

                // Checking for Credit Card Date
                if($n2 == "expiration") {
                    switch ($root) {
                        case 'month':
                            $value = date("m (M)", strtotime($value));
                            break;
                        case 'year':
                            $value = date("Y", strtotime($value));
                            break;
                    }
                }

                unset($val);
            } elseif(isset($person->$key)) {
                $value = $person->$key;
            }

            // $this->browser->waitForElementToBeClickable(
            //     WebDriverBy::id($fid)
            // );

            switch ($elType) {
                case 'input':
                    $this->browser->webui()->enterFieldData(
                        WebDriverBy::id($fid),
                        $value
                    );
                    $this->browser->webui()->changeFieldFocus();
                    break;
                case 'select':
                    $this->browser->webui()->setSelectValue(
                        WebDriverBy::id($fid),
                        // Special Case Country Values
                        // Maybe we should use web service to get country id?
                        ($root == "country_id") ? strtoupper($value) : $value
                    );
                    break;
            }
        }

        return true;
    }

    /**
     * [processFunnelUpsell description]
     * @return [type] [description]
     */
    public function processFunnelUpsell()
    {
        print("FUNCTION : processFunnelUpsell".PHP_EOL);

        // TODO: change sleep to a wait state on key data element
        // print("SLEEPING!".PHP_EOL);
        sleep(5);

        $upsell = Funnel\Helpers::randomlySelectUpsells($this->browser);

        $this->hbilog->writeToLogFile(array("upsell"=>$upsell));

        return true;
    }

    /**
     * [processFunnelDownsell description]
     * @return [type] [description]
     */
    public function processFunnelDownsell()
    {
        print("FUNCTION : processFunnelDownsell".PHP_EOL);

        SELF::processFunnelUpsell();
        return true;
    }

    /**
     * [getRequiredFormFields description]
     * @return [type] [description]
     */
    public function getRequiredFormFields()
    {
        print("FUNCTION : getRequiredFormFields".PHP_EOL);

        // TODO: This should go to the Helper Class
        // try {
            // $cssSlctr = 'label[for="'.$fid.'"] span.field-required';
            // $el  = $this->browser->driver()->findElement(
            //     WebDriverBy::cssSelector( $cssSlctr )
            // );
            // $reqfields[$fid] = $el->getAttribute("data-tooltip");
        // } catch (NoSuchElementException $e) {
            // Do nothing
        // }

    }

    /**
     * [fieldKeyTranslationTable description]
     * @param  String $lookup [description]
     * @return [type]         [description]
     */
    private function fieldKeyTranslationTable(String $lookup)
    {
        print("FUNCTION : fieldKeyTranslationTable".PHP_EOL);

        $key = null;

        try {
            $key = FIELDTABLE[$lookup];
        } catch (Exception $e) {
            throw new AutomationException('Invalid Form Field ID');
        }

        return $key;
    }

    /**
     * [getFunnelPageOffers description]
     * @param  [type] $pageId [description]
     * @return [type]         [description]
     */
    public function getFunnelPageOffers($pageId)
    {
        print("FUNCTION : getFunnelPageOffers".PHP_EOL);

        $fpo = SELF::getFunnelPageObject($pageId);

        return $fpo->page->offers;
    }

    /**
     * [processFunnelPresell description]
     * @return [type] [description]
     */
    public function processFunnelPresell()
    {
        print("FUNCTION : processFunnelPresell".PHP_EOL);

        $this->hbilog->writeToLogFile( array("DEBUG"=>array($this->page,$this->funnelPage) ));
        print_r(array("DEBUG"=>array($this->page,$this->funnelPage)));
        return false;
    }

    /**
     * [isRequiredFormField description]
     * @param  WebDriverBy $by [description]
     * @return boolean         [description]
     */
    public function isRequiredFormField(WebDriverBy $by)
    {
        print("FUNCTION : isRequiredFormField".PHP_EOL);

        try {
            $el  = $this->browser->driver()->findElement($by);
            return $el->getAttribute("data-tooltip");
        } catch (NoSuchElementException $e) {
            return false;
        }
    }

    /**
     * [getAddonsIdsForPage description]
     * @return [type] [description]
     */
    public function getAddonsIdsForPage()
    {
        print("FUNCTION : getAddonsIdsForPage".PHP_EOL);

        $addOnIds = array();
        $fpg      = $this->funnelPage->page;

        foreach ($fpg->offers as $offer) {
            if((bool)$offer->addon) {
                $addOnIds[$offer->offer_id] = $offer->offer_id;
            }
        }

        return $addOnIds;
    }

    public function processFinalReceipt()
    {
        print("FUNCTION : processFinalReceipt".PHP_EOL);
        // Get Each Item of
        // table.table-invoice tbody tr
        // -- [0] = Item
        // -- [1] = Qty
        // -- [2] = Retail
        // -- [3] = Price
        // -- [4] = Total
        //
        // table.table-total tbody tr
        // -- [0] = Label
        // -- [1] = Value

        $invCols = array('item','qty','retail','price','total');
        $ttlCols = array('label','amount');
        $invEls  = $this->browser->driver()->findElements(
            WebDriverBy::cssSelector('table.table-invoice tbody tr td')
        );
        $ttlEls  = $this->browser->driver()->findElements(
            WebDriverBy::cssSelector('table.table-total tbody tr td')
        );

        $i   = 0;
        $tmp = array();
        $inv = array();
        $ttl = array();

        foreach ($invEls as $el) {
            $tmp[ $invCols[$i] ] = $el->getText();
            if( $i++ >= count($invCols)-1 ) {
                $inv[] = $tmp;
                $tmp = array();
                $i=0;
            }
        }

        $tmp = array();
        $i=0;

        foreach ($ttlEls as $el) {
            $tmp[ $ttlCols[$i] ] = $el->getText();
            if( $i++ >= count($ttlCols)-1 ) {
                $lbl = trim( str_replace( ":", null ,$tmp['label'] ));
                $ttl[ $lbl ] = $tmp['amount'];
                $tmp = array();
                $i=0;
            }
        }

        // Clean up the data so its more understandable
        // Compare the values to what we recorded during the process
        // Compare the values to the Offer Values


        // Check to make sure values add up
        // Validate Subtotal
        $subTotal = 0;
        foreach ($inv as $itm) {
            $price     = Helpers::dollarsToFloat($itm['price']);
            $total     = Helpers::dollarsToFloat($itm['total']);

            $itemTotal = Helpers::dollarsToFloat( $itm['qty'] * $price );

            if( $itemTotal !==  Helpers::dollarsToFloat($total) ) {
                $this->hbilog->writeToLogFile(
                    array('TEST-FAILURE' => array(
                        "ORIGINAL CALCULATION"=>$itm['price'],
                        "DYNAMIC CALCULATION"=>$itemTotal,
                        "CALCULATION FORMULA"=> "floatval( ".$itm['qty']." * $price )"
                    )
                ));
            }

            $subTotal = Helpers::dollarsToFloat($subTotal+$itemTotal);
        }

        // TODO: Process this through a function
        if($subTotal !== Helpers::dollarsToFloat($ttl['Sub Total'])) {
            $this->hbilog->writeToLogFile( array('TEST-FAILURE'=>array("SUBTOTAL CALCULATION"=>$subTotal,'SUBTOTAL ON INVOICE'=>$ttl['Sub Total']) ));
        }

        // TODO: Process this through a function
        // if( ($ttl['Sub Total'] + $ttl['Shipping'] + $ttl['Tax']) !== $ttl['TOTAL'] ) {
        //     $this->hbilog->writeToLogFile( array('TEST-FAILURE'=>array("TOTALS CALCULATION"=>$ttl )));
        // }

        $this->hbilog->writeToLogFile( array($inv,$ttl) );
    }

}
