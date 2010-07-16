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
    
    //! Register this theme to core
    public static function register()
    {
        $theme_class = get_called_class();
        CMS_Core::get_instance()->register_theme( new $theme_class);
    }
}

?>
