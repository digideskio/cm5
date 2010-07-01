<?php

//! Interface to implement themes
abstract class CMS_Theme
{
    //! Array with theme info
    abstract public function info();
    
    //! Get theme layout class
    abstract public function get_layout_class();
    
    //! Initialize theme
    public function init()
    {
        Layout::assign('default', $this->get_layout_class());
    }
}

?>
