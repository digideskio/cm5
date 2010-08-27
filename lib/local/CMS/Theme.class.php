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
        $theme_class = $this->get_layout_class();
        if (!eval("return isset($theme_class::\$theme_nickname);"))
            throw new RuntimeException('Theme Layout class must have static property "theme_nickname"!');
        Layout::assign('default', $theme_class);
    }
    
    public function on_save_config()
    {
        if (GConfig::get_instance()->site->theme == $this->config_nickname())
            CMS_Core::get_instance()->invalidate_page_cache();
    }
}

?>
