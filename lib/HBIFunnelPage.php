<?php
namespace HBI;

use HBI\HBIHelper;
use HBI\Funnel\PgDataObject;
use HBI\Funnel\Helpers;

use \Facebook\WebDriver\WebDriverBy;

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
    }

    private function setPageDataObject()
    {
        $pg         = json_encode($this->browser->webui()->getPageDataPoints());
        $this->page = new PgDataObject( $pg );

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

            default:
                $ret = false;
                break;
        }


        return $ret;
    }

    public function processSalesPageForm(HBIPerson $person, $formType)
    {
        // Do we open a modal or go to new page? ($formType)
        $this->browser->webui()->clickButton("a.modal-toggle img.responsive-img");

        // Wait for Modal to load
        $this->browser->waitForElement(
            WebDriverBy::cssSelector(".modal.modal-scrollable")
        );

        return $this->processFunnelForm($person);
    }

    public function processOrderForm(HBIPerson $person)
    {
        $ret = $this->processFunnelForm($person);
        // Check for Add-Ons
        Funnel\Helpers::randomlySelectAddons($this->browser);

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

        foreach ($fields as $field) {
            $fid    = $field->getAttribute("id");
            $b      = explode("-",$fid);
            $prefix = count($b) > 1 ? $b[0] : null;
            $root   = isset($b[1]) ? $b[1] : $b[0];
            $elType = $field->getTagName();
            $key    = $this->fieldKeyTranslationTable($root);
            $val    = array();

            if(!$field->isDisplayed()) {continue;}

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
        sleep(10);
        // Submit Form
        // Check for Form Errors
        // Correct Form items & resubmit?
        // Return False if form still incorrect
        // Return True if going to next step
        return true;
    }

    public function processFunnelUpsell()
    {
        $upsell = Funnel\Helpers::randomlySelectUpsells($this->browser);
        sleep(5);

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

}
