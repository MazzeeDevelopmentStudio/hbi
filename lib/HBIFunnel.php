<?php
namespace HBI;

/**
*
*/
class HBIFunnel extends HBIBasicObject
{
    public $id;
    public $name;
    public $description;
    public $stages;

    public function __construct($json = false) {
        if ($json) $this->set(json_decode($json, true));
    }

    private function set($data) {
        foreach ($data AS $key => $value) {
            if (is_array($value)) {
                $sub = new HBIBasicObject;
                $sub->set($value);
                $value = $sub;
            }
            $this->{$key} = $value;
        }
    }

}
