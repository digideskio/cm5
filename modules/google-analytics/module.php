<?php

class CMS_Module_GoogleAnalytics extends CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'google-analytics',
            'title' => 'Google Analytics Tracker',
            'description' => 'Add tracking code inside pages for google analytics.'
        );
    }
    
    //! Configuration options of this module
    public function config_options()
    {
        return array(
            'property_id' => array('display' => 'Site property id as defined by google analytics.'),
            'inform_admin' => array('display' => 'Monitor admin activity.', 'type' => 'checkbox'),
        );
    }
    
    //! Get default configuration
    public function default_config()
    {
        return array(
            'inform_admin' => false
        );
    }
    
    //! On configuration update we must invalidate cache
    public function on_save_config()
    {
        CMS_Core::get_instance()->invalidate_page_cache(null);
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        if ($this->get_config()->property_id)
            $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
    }
    
    //! Handler for pre rendering
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        $p->body .= html_ga_code($this->get_config()->property_id, true);
    }
}

CMS_Module_GoogleAnalytics::register();
?>
