<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */

class CM5_Module_GoogleAnalytics extends CM5_Module
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
            'property_id' => array('label' => 'Site property id as defined by google analytics.'),
//            'inform_admin' => array('display' => 'Monitor admin activity.', 'type' => 'checkbox'),
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
        CM5_Core::get_instance()->invalidate_page_cache(null);
    }
    
    //! Initialize module
    public function init()
    {
        $c = CM5_Core::get_instance();
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

CM5_Module_GoogleAnalytics::register();
