<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\Admin\Helpers;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIService;
use HBI\HBIServices;

use joshtronic\LoremIpsum;

/**
 *
 */
class AddService extends Actions
{
    private $browser;
    private $service;
    private $log;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $service=null)
    {
        parent::__construct($browser);
        
        $this->browser = $browser;
        $this->log     = &$GLOBALS['HBILog'];
        $this->service = !empty($service) ? $service : $this->defineRandomService();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->service, true) );
    }

    public function add()
    {
        $this->openAddPanel();
        $this->addServiceDataToForm();

        $this->clickSaveButton();
        $this->clickDoneButton();
    }

    // TODO: We should just create very random data instead of
    // relying on the json file
    public function defineRandomService()
    {
        $svc     = new HBIServices;
        $service = $svc->buildCollection(1);

        if($service->isMissingProperty()) {
            $service->sku         = SELF::RandomSku();
            $service->name        = SELF::RandomName();
            $service->description = SELF::RandomDescription();
        }

        $this->testifyServiceDetails($service);

        $service->retail = Helpers::getRandomDollarAmount();
        $service->cogs   = Helpers::getRandomDollarAmount();
        $service->notes  = SELF::RandomDescription();

        $this->log->writeToLogFile($service);
        print_r($service);

        return $service;

    }

    public function addServiceDataToForm()
    {
        // Check if modal is now visible
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('div.modal-content')
        );

        // We need to make sure our tab is in view first
        $this->clickTab("Step 1: Basic Info");

        // Enter field Values
        // TODO: Move to dynamic referencing model like in Funnels
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('sku'),
            $this->service->sku
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="name"]'),
            $this->service->name
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('description'),
            $this->service->description
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('retail'),
            !rand(0,3) ?  floatval($this->service->retail) : $this->service->retail
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('cogs'),
            !rand(0,3) ?  floatval($this->service->cogs) : $this->service->cogs
        );

        $this->clickTab("Step 2: Notes");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('admin_notes'),
            $this->service->notes
        );

        // TODO: Test the following data points
        // Purchase Actions
        // Return Actions
        // Restrictions

    }

    public static function addServiceNotesToForm()
    {

    }

    public static function setServiceRestrictions()
    {
        

    }

    public static function testifyServiceDetails(HBIService &$service, $prefix="QA-")
    {
        $service->sku         = sprintf('%s%s%s', $prefix, $service->sku, rand(0,100));
        $service->name        = sprintf('[%sTEST] %s', $prefix, $service->name);

        if(!empty($service->description)) {
            $service->description = sprintf('[%sTEST Service]%s%s', $prefix, PHP_EOL, $service->description);    
        }
    }

    public static function RandomSku()
    {
        $lipsum = new LoremIpsum();
        $words  = $lipsum->words( rand(2,4) );
        
        $random = rand(000,999);

        return preg_replace('/\s+/', '-', $words.' '.$random);
    }

    public static function RandomName()
    {
        $lipsum = new LoremIpsum();
        $words  = $lipsum->words( rand(1,5) );

        return $words;
    }    

    public static function RandomDescription()
    {
        $lipsum = new LoremIpsum();

        return $lipsum->paragraphs( rand(0,1) );
    }

    /**
     * TODO: Move this to WebUI or to HBIPanel
     * [clickTab description]
     * @param  [type] $tabname [description]
     * @return [type]          [description]
     */
    protected function clickTab($tabname)
    {
        $this->browser->webui()->clickTab($tabname);
    }



}
