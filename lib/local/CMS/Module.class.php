<?php

interface CMS_Module
{
    //! Array with module info
    public function info();
    
    //! Initialize module
    public function init();
}
