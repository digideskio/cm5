<?php

//! Interface to implement modules
interface CMS_Module
{
    //! Array with module info
    public function info();
    
    //! Initialize module
    public function init();
}
