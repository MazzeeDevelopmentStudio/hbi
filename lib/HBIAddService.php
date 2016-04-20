<?php
namespace HBI;

/**
*
*/
class HBIAddService
{
    private $_driver;
    private $_service;
    private $_webui;

    function __construct($driver, $service)
    {
        $this->setService($service);

        $this->_driver = $driver;
        $this->_webui = new HBIWebUI($this->_driver);
    }

    function setService($service)
    {
        $this->_service = $service;
    }

    function getService()
    {
        return $this->_service;
    }

    public function openAddPanel()
    {
        // There is a timing issue so we need to wait a second
        sleep(1);
        // TODO: Add ID to "Add" button
        $this->_webui->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');
    }

    public function addServiceDataToForm()
    {
        // There is a timing issue so we need to wait a second
        sleep(1);

        $this->_webui->enterFieldData("sku", $this->_service->sku, "id");
        $this->_webui->enterFieldData("name", $this->_service->name, "name");
        $this->_webui->enterFieldData("description", $this->_service->description, "id");
        $this->_webui->enterFieldData("retail", $this->_service->retail, "id");
        // $this->_webui->enterFieldData("cogs", $this->_service->cogs, "id");
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
