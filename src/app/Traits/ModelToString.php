<?php
namespace App\Traits;

trait ModelToString {
    /**
     * Returns a simple string representation of this object.
     * 
     * @return string
     */
    public function __toString()
    {
        $rClass = new \ReflectionClass($this);
        $shortName = $rClass->getShortName();
        $id = '#';
        if ($this->id) {
            $id = $this->id;
        }
        return "[{$shortName}:{$id}]";
    }
    
    /**
     * Returns a string representing the object and a friendly name for the
     * object.
     * 
     * @return string
     */
    public function getFullName()
    {
        $name = $this->getFriendlyName();
        $return = (string) $this;
        if ($name) {
            $return .= " {$name}";
        }
        return $return;
    }
    
    /**
     * Returns a friendly name for the object from one or more of its
     * properties. Can be overridden in descendant classes for more intelligent
     * behaviour.
     * 
     * @return string
     */
    protected function getFriendlyName()
    {
        if (property_exists($this, 'name')) {
            return $this->name;
        } elseif (method_exists($this, 'getName')) {
            return $this->getName();
        }
    }
}