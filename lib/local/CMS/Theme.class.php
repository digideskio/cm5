<?php

//! Interface to implement themes
abstract class CMS_Theme extends CMS_Configurable
{
    public function config_nickname()
    {
        return $this->info_property('nickname');
    }
    
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
