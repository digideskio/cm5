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
    
    public function default_config()
    {
        $version = CMS_Core::get_instance()->get_version();
        return array(
            'page-background-color' => '4B484F',
            'article-background-color' => 'F6F5FF',
            'article-text-color' => '292929',
            'menu-background-color' => 'F0F0F0',
            'menu-text-color' => '0A0A0A',
            'menu-selected-background-color' => 'D20000',
            'menu-selected-text-color' => 'FFFFFF',
            'footer' => "<a href=\"http://cm5.0x0lab.org\">CM5 v{$version[0]}.{$version[1]}.{$version[2]}</a>",
            'favicon-url' => '',
            'extra-css' => '',
        );
    }
    
    public function config_options()
    {
        return array(
            'page-background-color' => array('display' => 'Page background color:', 'type' => 'color'),
            'article-background-color' => array('display' => 'Article background color:', 'type' => 'color'),
            'article-text-color' => array('display' => 'Article text color:', 'type' => 'color'),
            'menu-background-color' => array('display' => 'Menu background color:', 'type' => 'color'),
            'menu-text-color' => array('display' => 'Menu text color:', 'type' => 'color'),
            'menu-selected-background-color' => array('display' => 'Menu selected background color:', 'type' => 'color'),
            'menu-selected-text-color' => array('display' => 'Menu selected text color:', 'type' => 'color'),
            'favicon-url' => array('display' => 'Favicon url:'),
            'footer' => array('display' => 'Footer content:', 'type' => 'textarea'),
            'extra-css' => array('display' => 'Extra css to be included:', 'type' => 'textarea')
        );
    }
    public function get_layout_class()
    {
        return 'DefaultThemeLayout';
    }
}

DefaultTheme::register();
?>
