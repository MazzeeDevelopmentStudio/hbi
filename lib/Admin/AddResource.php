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
    private $resource;
    private $log;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $resource=null)
    {
        parent::__construct($browser);
        
        $this->browser = $browser;
        $this->log      = &$GLOBALS['HBILog'];
        $this->resource = !empty($resource) ? $resource : $this->defineRandomResource();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->resource, true) );
    }

    public function add()
    {
        $this->openAddPanel();
        $this->addResourceDataToForm();

        $this->clickSaveButton();
        $this->clickDoneButton();
    }

    // TODO: We should just create very random data instead of
    // relying on the json file
    public function defineRandomResource()
    {
        $res            = new HBIResources;
        $resource       = $res->buildCollection(1);
        $resource->cogs = Helpers::getRandomDollarAmount();
        $resource->type = Helpers::getRandomProductType($this->browser);

        $this->testifyResourceDetails($resource);

        $this->log->writeToLogFile($resource);

        return $resource;
    }

    public function openAddPanel()
    {
        // Need to wait for button
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );
        print('Clicking the ADD Resource Button'.PHP_EOL);
        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );
    }

    public function addResourceDataToForm()
    {
        // Check if modal is now visible
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('div.modal-content')
        );

        // Enter field Values
        // TODO: Move to dynamic referencing model like in Funnels
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('sku'),
            $this->resource->sku
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="name"]'),
            $this->resource->name
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('description'),
            $this->resource->description
        );

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('cogs'),
            !rand(0,3) ?  floatval($this->resource->cogs) : $this->resource->cogs
        );

        $this->setResourceType( $this->resource->type );

        if($this->resource->type == "Physical Product") {
            // if Physical -> Provide Product Dimensions
            $this->setResourceDimensions();
        } elseif($this->resource->type == "Digital Product") {
            // If Digital -> Provide Download URL
            $this->setResourceDownloadUrl();
        }
    }

    public function testifyResourceDetails(HBIResource &$resource, $prefix="QA-")
    {
        $resource->sku         = sprintf('%s%s%s', $prefix, $resource->sku, rand(0,100));
        $resource->name        = sprintf('[%sTEST] %s', $prefix, $resource->name);
        $resource->description = sprintf('[%sTEST Resource]%s%s', $prefix, PHP_EOL, $resource->description);
    }

    protected function setResourceCategory($category)
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

    protected function setResourceType($type)
    {
        $this->browser->webui()->clickButton(
            WebDriverBy::id("s2id_item_type_id")
        );
        $this->browser->webui()->clearField("s2id_autogen1_search","id");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1_search"),
            $type
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setResourceDimensions()
    {
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="length"]'),
            $this->product->length
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="height"]'),
            $this->product->height
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="depth"]'),
            $this->product->depth
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::cssSelector('[name="weight"]'),
            $this->product->weight
        );
    }

    protected function setResourceDownloadUrl()
    {
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id('download_url'),
            $this->product->downloadurl
        );
    }


}
