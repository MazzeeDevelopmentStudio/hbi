<?php
namespace HBI;

use ReflectionClass;
use ReflectionProperty;

class HBIBasicObject
{
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

    public function setValue($type, $value)
    {
        $this->$type = $value;
    }

    public function getValue($type)
    {
        return $this->$type;
    }

    public function getProperties()
    {
        // Problem with old method, we got all
        // properties - even private properties.
        // We should only show public properties.
        // $ov = get_object_vars($this);
        // return array_keys($ov);

        $op      = array();
        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $op[] = $prop->name;
        }

        return $op;

    }

    public function isMissingProperty()
    {
        foreach ($this->getProperties() as $property) {
            if(empty($this->$property)) {
                return true;
            }
        }

        return false;
    }
}
