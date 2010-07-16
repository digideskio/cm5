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
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        if (Config::get('site.google_analytics'))
            $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
    }
    
    //! Handler for pre rendering
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        $p->body .= html_ga_code(Config::get('site.google_analytics'), true);
    }

}

CMS_Module_GoogleAnalytics::register();
?>
