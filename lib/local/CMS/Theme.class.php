<?php

//! Interface to implement themes
abstract class CMS_Theme extends CMS_Module
{
    //! Get theme layout class
    abstract public function get_layout_class();
 
    //! Type of module
    public function module_type()
    {
        return 'theme';
    }
    
    //! Initialize theme
    public function init()
    {
        Layout::assign('default', $this->get_layout_class());
    }
}

?>
