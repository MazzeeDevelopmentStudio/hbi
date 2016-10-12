<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBITaxes;

/**
 *
 */
class AddTax extends Actions
{
    private $browser;
    private $taxset;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser)
    {
        parent::__construct($browser);

        $this->browser = $browser;

        $this->createTaxSet();
  
        print("TAX SET  : ".json_encode($this->taxset).PHP_EOL);
    }

    /**
     * [add description]
     */
    public function add()
    {
        print("FUNCTION : HBI::AddTax::add".PHP_EOL);

        $this->openAddPanel();
        $this->addTaxDataToForm();
        $this->clickSaveButton();
        $this->clickDoneButton();
    }

    /**
     * [edit description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit($id)
    {
        // TODO: Click the ID Link
        // TODO: Sort by ID, and calculate the position in pagination
        // <a href="/dashboard/settings/tax/56">
        // TODO: Call addTaxDataToForm 

    }

    /**
     * [createTaxSet description]
     * @return [type] [description]
     */
    public function createTaxSet()
    {
        print("FUNCTION : HBI::AddTax::createTaxSet".PHP_EOL);

        $taxes        = new HBITaxes();
        $tax          = $taxes->buildCollection(1);
        $tax->year    = rand(1950, 2016);
        $this->taxset = $tax;

        unset($taxes);
    }

    /**
     * 
     */
    public isExistingTaxSet()
    {
        // Search State/Year combination
    }

    /**
     * [addPersonDataToForm description]
     */
    protected function addTaxDataToForm()
    {
        $this->setTaxState($this->taxset->state_id);

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("state_tax"),
            $this->taxset->state
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("county_tax"),
            $this->taxset->country
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("city_tax"),
            $this->taxset->city
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("year"),
            $this->taxset->year
        );
    }

    /**
     * [setTaxState description]
     * @param [type] $state [description]
     */
    protected function setTaxState($state)
    {
        $this->browser->waitForElementToBeClickable(
            WebDriverBy::id("s2id_state")
        );

        $this->browser->clickElement(
            WebDriverBy::id("s2id_state")
        );
        $this->browser->webui()->clearField("s2id_autogen1_search","id");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1_search"),
            $state
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    /**
     * [clickSaveButton description]
     * @return [type] [description]
     * Added since classes used differ
     */
    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-xs.btn-info.pull-right.mr20')
        );

        // TODO: Add Try/Catch
        // TODO: Validate Data (via API)

        print("ACTION   : Clicked SAVE Button".PHP_EOL);
    }

    /**
     * [clickDoneButton description]
     * @return [type] [description]
     * Added since classes used differ
     */
    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".btn.btn-default.btn-xs.pull-right.dismissButton")
        );

        // TODO: Validate that the window has closed

        print("ACTION   : Clicked DONE Button".PHP_EOL);
    }


}