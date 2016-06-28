<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIProducts;

/**
 *
 */
class AddProduct extends Actions
{
    private $browser;
    private $product;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $product=null)
    {
        $this->browser = $browser;
        $this->product = !empty($product) ? $product : $this->defineRandomProduct();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->product, true) );
    }

    public function defineRandomProduct()
    {
        $product       = new HBIProducts;
        $collection   = $product->buildCollection(1);

        print_r($collection);

        return $collection;

    }

    public function openAddPanel()
    {
        // There is a timing issue so we need to wait a second
        sleep(1);
        // TODO: Add ID to "Add" button
        $this->_webui->clickButton(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );
    }

    public function addProductDataToForm()
    {
        $this->_webui->enterFieldData("sku", $this->_service->sku, "id");
        $this->_webui->enterFieldData("name", $this->_service->name, "name");
        $this->_webui->enterFieldData("description", $this->_service->description, "id");
        $this->_webui->enterFieldData("retail", $this->_service->retail, "id");
        // $this->_webui->enterFieldData("cogs", $this->_service->cogs, "id");
    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->_webui->clickButton(
            WebDriverBy::cssSelector('.btn.btn-xs.btn-info.pull-right.mr20.btn-save')
        );

        sleep(1);
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->_webui->clickButton(
            WebDriverBy::cssSelector(".btn.btn-default.btn-xs.pull-right.btn-dismiss")
        );

        sleep(1);
    }

    public function refreshPage()
    {
        $this->_webui->refreshPage();
    }
}
