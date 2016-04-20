<?php
namespace HBI;

/**
*
*/
class HBIService
{
    private $_sku;
    private $_name;
    private $_description;
    private $_category;
    private $_retail;
    private $_cogs;
    private $_type;
    private $_length;
    private $_height;
    private $_depth;
    private $_weight;
    private $_threshold;
    private $_quantity;

    /**
     * [__construct description]
     */
    function __construct()
    {
        # nothign to do
    }


    public function product() {}

    protected function setSku($sku) { $this->_sku = $sku; }
    public function getSku() { return $this->_sku; }

    protected function setName($name) { $this->_name = $name; }
    public function getName() { return $this->_name; }

    protected function setDescription($desc) { $this->_description = $desc; }
    public function getDescription() { return $this->_description; }

    protected function setCategory($cat) { $this->_category = $cat; }
    public function getCategory() { return $this->_category; }

    protected function setRetail($retail) { $this->_retail = $retail; }
    public function getRetail() { return $this->_retail; }

    protected function setCogs($cogs) { $this->_cogs = $cogs; }
    public function getCogs() { return $this->_cogs; }

    protected function setType($type) { $this->_type = $type; }
    public function getType() { return $this->_type; }

    protected function setLength($length) { $this->_length = $length; }
    public function getLength() { return $this->_length; }

    protected function setHeight($height) { $this->_height = $height; }
    public function getHeight() { return $this->_height; }

    protected function setDepth($depth) { $this->_depth = $depth; }
    public function getDepth() { return $this->_depth; }

    protected function setWeight($weight) { $this->_weight = $weight; }
    public function getWeight() { return $this->_weight; }

    protected function setThreshold($threshold) { $this->_threshold = $threshold; }
    public function getThreshold() { return $this->_threshold; }

    protected function setQuantity($quantity) { $this->_quantity = $quantity; }
    public function getQuantity() { return $this->_quantity; }


    function setAttributes($attributes) {
        $this->setSku($attributes->sku); //required
        $this->setName($attributes->name); //required
        $this->setDescription($attributes->description); //required
        $this->setRetail($attributes->retail); //required
        $this->setType($attributes->type); //required
        $this->setThreshold($attributes->threshold); //required
        $this->setQuantity($attributes->quantity); //required

        // $this->setCategory($attributes->category);
        // $this->setCogs($attributes->cogs);
        // $this->setLength($attributes->length);
        // $this->setHeight($attributes->height);
        // $this->setDepth($attributes->depth);
        // $this->setWeight($attributes->weight);
    }
}
