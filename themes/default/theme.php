<?php
require_once(dirname(__FILE__) . '/layout.php');

class DefaultTheme extends CMS_Theme
{
    public function info()
    {
        return array(
            'nickname' => 'default',
            'title' => 'Default theme',
            'description' => 'Default theme that comes with CMS.'
        );
    }
    
    public function get_layout_class()
    {
        return 'DefaultThemeLayout';
    }
}

CMS_Core::get_instance()->register_theme(new DefaultTheme());
?>
