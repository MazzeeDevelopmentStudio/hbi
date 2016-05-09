<?php
namespace HBI;


/**
*
*/
class HBIBuildJsonAddressData
{
    private $_output;

    function __construct($output)
    {
        $this->_output = $output;
    }

    public function processGoogleGeoFile($file)
    {
        $json = file_get_contents($file);
        $coll = json_decode($json);

        foreach ($coll->results as $addr) {
            if( isset($addr->types[0]) && $addr->types[0] == 'street_address') {
                $address = $this->getAddressFromAddressComponent($addr->address_components);

                if(!$address->isMissingProperty()) {
                    $this->appendAddressDataFile($address);
                }
            }
        }
    }

    public function getAddressFromAddressComponent($addrObj)
    {
        $addr = new HBIAddress;

        foreach ($addrObj as $component) {
            if(!isset($component->types[0])) {continue;}
            if(in_array( $component->types[0], $addr->getProperties() )) {
                $sz = $component->types[0] == "route" ? "short_name" : "long_name";
                $addr->setValue($component->types[0], $component->$sz);
            }
        }

        return $addr;
    }

    public function updateAddressDataFile($address)
    {
        $file = file_get_contents($this->_output);
        $data = json_decode($file);

        unset($file);//prevent memory leaks for large json.

        //insert data here
        $data[] = $address;

        //save the file
        file_put_contents($this->_output,json_encode($data));
        unset($data);//release memory
    }

    public function appendAddressDataFile($address)
    {
        file_put_contents($this->_output, json_encode($address).','.PHP_EOL, FILE_APPEND);
    }

    public function jsonifyAddressDataFile()
    {
        $data      = file_get_contents($this->_output);
        $validjson = sprintf('[%s]', $data);

        unset($data);//prevent memory leaks for large json.

        file_put_contents( $this->_output, $validjson);
        unset($validjson);//release memory
    }
}

?>
