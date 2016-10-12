<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIVendors;
use HBI\HBIAddresses;

/**
 *
 */
class AddVendor extends Actions
{
    private $browser;
    private $vendor;

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

        $this->defineRandomVendor();
  
        print("VENDOR   : ".json_encode($this->vendor).PHP_EOL);
    }

    /**
     * [add description]
     */
    public function add()
    {
        print("FUNCTION : HBI::AddVendor::add".PHP_EOL);

        $this->openAddPanel();
        $this->defineRandomVendor();
        $this->clickSaveButton();
        $this->clickDoneButton();
    }

    public function defineRandomVendor()
    {
        print("FUNCTION : HBI::AddVendor::createVendor".PHP_EOL);

        $vndrs        = new HBIVendors();
        $vendor       = $vndrs->buildCollection(1);
        $this->vendor = $vendor;

        $this->vendor->type = $this->defineRandomType();

        $this->defineAddress();
        $this->defineContactInformation();


        unset($vndrs);
    }

    /**
     * [defineRandomType description]
     * @return [type] [description]
     */
    protected function defineRandomType()
    {
        $options = $this->browser->webui()->getHiddenOptions("type");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }


    public function defineAddress()
    {
        $addrs                 = new HBIAddresses();
        $address               = $addrs->buildCollection(1);
        $this->vendor->address = $address;

        unset($addrs);
    }

    public function defineContactInformation()
    {
        $contact = new \stdClass();

        $contact->primary_phone   = HBIHelper::createRandomPhoneNumber(); // Primary Phone - primary_phone
        $contact->secondary_phone = HBIHelper::createRandomPhoneNumber(); // Secondary Phone - secondary_phone
        $contact->fax             = HBIHelper::createRandomPhoneNumber(); // Fax - fax
        $contact->primary_email   = HBIHelper::createRandomEmail(null, true); // Primary Email - primary_email
        $contact->secondary_email = HBIHelper::createRandomEmail(null, true); // Secondary Email - secondary_email

        $this->vendor->contact = $contact;

        unset($contact);
    }

    public function addVendorDataToForm()
    {
        // name
        // url
        // type
    }

    public function addVendorAddressToForm()
    {
        // address1
        // address2
        // address3
        // city
        // state
        // zip
    }

    public function addVendorContactInformationToForm()
    {
        // Primary Phone - primary_phone
        // Secondary Phone - secondary_phone
        // Fax - fax
        // Primary Email - primary_email
        // Secondary Email - secondary_email
    }


}