<?php
namespace App\Traits;

use App\Helpers\LogHelper;

trait LoggableModel {
    protected $_logHelper;
    
    /**
     * Returns a log helper instance for this object.
     * 
     * @return \App\Helpers\LogHelper
     */
    public function log() {
        if ($this->_logHelper === null) {
            $this->_logHelper = new LogHelper($this);
        }
        return $this->_logHelper;
    }
}