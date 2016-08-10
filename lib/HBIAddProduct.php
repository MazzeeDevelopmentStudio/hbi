<?php
namespace HBI;

/**
*
*/
class HBIAddProduct
{
    private $_driver;
    private $_product;
    private $_webui;

    function __construct($driver, $product)
    {
        $this->setProduct($product);

        $this->_driver = $driver;
        $this->_webui = new HBIWebUI($this->_driver);
    }

    function setProduct($product)
    {
        $this->_product = $product;
    }

    function getProduct()
    {
        return $this->_product;
    }

    public function openAddPanel()
    {
        // There is a timing issue so we need to wait a second
        sleep(1);
        // TODO: Add ID to "Add" button
        $this->_webui->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');
    }

    public function addProductDataToForm()
    {
        $this->_webui->enterFieldData("sku", $this->_product->sku, "id");
        $this->_webui->enterFieldData("name", $this->_product->name, "name");
        $this->_webui->enterFieldData("description", $this->_product->description, "id");
        $this->_webui->enterFieldData("retail", $this->_product->retail, "id");
        // $this->_webui->enterFieldData("cogs", $this->_product->cogs, "id");
    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->_webui->clickButton('.btn.btn-xs.btn-info.pull-right.mr20.btn-save');

        sleep(1);
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->_webui->clickButton(".btn.btn-default.btn-xs.pull-right.btn-dismiss");

        sleep(1);
    }

    public function refreshPage()
    {
        $this->_webui->refreshPage();
    }

}
