<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\Admin\Helpers;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIResource;
use HBI\HBIResources;

/**
 *
 */
class AddResource extends Actions
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
        $prod    = new HBIServices;
        $service = $prod->buildCollection(1);

        $this->testifyServiceDetails($service);

        $service->retail   = Helpers::getRandomDollarAmount();
        $service->cogs     = Helpers::getRandomDollarAmount();
        $service->category = Helpers::getRandomServiceCategory($this->browser);
        $service->type     = Helpers::getRandomServiceType($this->browser);

        $this->log->writeToLogFile($service);
        print_r($service);

        return $service;

    }

    public function openAddPanel()
    {
        // Need to wait for button
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );

        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );
    }

    public function addServiceDataToForm()
    {
        // Check if modal is now visible
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('div.modal-content')
        );

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

        $this->setServiceCategory( $this->service->category );
        $this->setServiceType( $this->service->type );

        // If Digital -> Provide Download URL
        // $this->setServiceDownloadUrl()
        //
        // if Physical -> Provide Service Dimensions
        // $this->setServiceDimensions()

    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-xs.btn-info.pull-right.mr20.btn-save')
        );

        sleep(1);
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".btn.btn-default.btn-xs.pull-right.btn-dismiss")
        );

        sleep(1);
    }

    public function refreshPage()
    {
        $this->browser->webui()->refreshPage();
    }

    public static function testifyServiceDetails(HBIService &$service, $prefix="QA-")
    {
        $service->sku         = sprintf('%s%s%s', $prefix, $service->sku, rand(0,100));
        $service->name        = sprintf('[%sTEST] %s', $prefix, $service->name);
        $service->description = sprintf('[%sTEST Service]%s%s', $prefix, PHP_EOL, $service->description);
    }

    protected function setServiceCategory($category)
    {
        $this->browser->webui()->clearField("s2id_autogen1","id");
        $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            $category
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setServiceType($type)
    {
        $this->browser->webui()->clearField("s2id_autogen2","id");
        // $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen2"),
            $type
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setServiceDimensions()
    {

    }

    protected function setServiceDownloadUrl()
    {

    }


}
