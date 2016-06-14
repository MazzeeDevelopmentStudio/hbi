<?php
namespace HBI;

use HBI\HBIHelper;
use HBI\Funnel\PgDataObject;
use HBI\Funnel\Helpers;

use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\Exception\NoSuchElementException;

/**
*
*/
class HBIFunnelPage
{
    private $browser;
    private $hbilog;
    private $page;
    public  $funnelPage;

    public function __construct(HBIBrowser $browser, HBILog $hbilog)
    {
        $this->browser = $browser;
        $this->hbilog  = $hbilog;

        $this->initiateNewPage();
    }

    public function initiateNewPage()
    {
        $this->setPageDataObject();

        $pageId = $this->page->pageId;
        $json   = HBIHelper::getDataFromHBICoreAPI(
                    'api/funnel/get-funnel-page',
                    array('page_id'=>$pageId)
                );

        $this->funnelPage = (object)json_decode($json);

        $this->hbilog->writeToLogFile( array("FunnelPage"=>$this->funnelPage) );
    }

    private function setPageDataObject()
    {
        $pg         = json_encode($this->browser->webui()->getPageDataPoints());
        $this->page = new PgDataObject( $pg );

        // DEBUG OUTPUT
        print("Monitoring Page: ".$pg.PHP_EOL);

        unset($pg);
    }

    public function processPage(HBIPerson $person)
    {
        $ret      = false;
        $formType = null;

        switch ($this->page->stageType) {
            case 'SalesPage':
                $ret = SELF::processSalesPageForm($person, $formType);
                break;

            case 'OrderForm':
                $ret = SELF::processOrderForm($person);
                break;

            case 'Upsell':
                SELF::processFunnelUpsell();
                $ret = true;
                break;

            case 'ThankYou':
                // We Return false because we are done;
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

    public function processSalesPageForm(HBIPerson $person, $formType)
    {
        try {
            $this->browser->driver()->findElement(
                WebDriverBy::cssSelector("div.purchase-button a.modal-toggle")
            );
            $formType = "ajaxsubmit";
        } catch (NoSuchElementException $e) {
            $formType = "checkoutsubmit";
        }

        $this->browser->webui()->clickButton("div.purchase-button");

        // Wait for form to be useable
        $this->browser->waitForElement(
            WebDriverBy::cssSelector("form.".$formType)
        );


        $ret = $this->processFunnelForm($person);

        try {
            $this->browser->webui()->clickButton("button#submit");
        } catch (NoSuchElementException $e) {
            // Do Nothing?
        }

        return $ret;
    }

    public function processOrderForm(HBIPerson $person)
    {
        $ret = $this->processFunnelForm($person);
        // Check for Add-Ons
        $addons = Funnel\Helpers::randomlySelectAddons($this->browser);

        $this->hbilog->writeToLogFile(array("addons"=>$addons));

        // DEBUG OUTPUT
        foreach ($addons as $addon) {
            print(sprintf(
                "Addon: %s".PHP_EOL,
                str_replace("addon-checkbox_",
                null,
                $addon
            )));
        }

        // Submit Order
        $this->browser->clickElement(
            WebDriverBy::id("submitcheckoutform")
        );

        return $ret;
    }

    public function processFunnelForm(HBIPerson $person)
    {

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



        sleep(15);
        // Submit Form
        // Check for Form Errors
        // Correct Form items & resubmit?
        // Return False if form still incorrect
        // Return True if going to next step
        return true;
    }

    public function processFunnelUpsell()
    {
        sleep(5);

        $upsell = Funnel\Helpers::randomlySelectUpsells($this->browser);

        $this->hbilog->writeToLogFile(array("upsell"=>$upsell));

        return true;
    }

    public function processFunnelDownsell()
    {
        SELF::processFunnelUpsell();
        return true;
    }


    private function fieldKeyTranslationTable(String $lookup)
    {
        $key = null;

        try {
            $key = FIELDTABLE[$lookup];
        } catch (Exception $e) {
            throw new AutomationException('Invalid Form Field ID');
        }

        return $key;
    }

    public function getFunnelPageOffers($pageId)
    {
        $fpo = SELF::getFunnelPageObject($pageId);

        return $fpo->page->offers;
    }

    public function processFunnelPresell()
    {
        $this->hbilog->writeToLogFile( array("DEBUG"=>array($this->page,$this->funnelPage) ));
        print_r(array("DEBUG"=>array($this->page,$this->funnelPage)));
        return false;
    }

    public function isRequiredFormField(WebDriverBy $by)
    {
        try {
            $el  = $this->browser->driver()->findElement($by);
            return $el->getAttribute("data-tooltip");
        } catch (NoSuchElementException $e) {
            return false;
        }
    }

}
