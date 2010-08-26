<?php

//! Base class to implement theme layout
class CMS_ThemeLayout extends Layout
{
    
    //! The configuration of the module/theme
    private $module_config = null;
    
    //! Get the configuration of this theme
    public function get_config()
    {
        if ($this->module_config !== null)
            return $this->module_config;
        return CMS_Core::get_instance()->get_module(get_static_var(get_class($this), 'theme_nickname'))->get_config();
    }
}

?>
