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

    public function randItemFromJsonFile($file) {
        print_r($file);
        $maxLineLength = 4096;
        $handle        = @fopen($file, "r");
        $randomItem    = null;

        if ($handle) {
            $random_line = null;
            $line        = null;
            $count       = 0;

            while (($line = fgets($handle, $maxLineLength)) !== false) {
                $count++;
                // P(1/$count) probability of picking current line as random line
                if(rand() % $count == 0) {
                  $randomItem = $line;
                }
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
                fclose($handle);
                return null;
            } else {
                fclose($handle);
            }

        }

        return json_decode( rtrim( trim($randomItem), ",") );
    }


}
