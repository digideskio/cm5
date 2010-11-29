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

class UI_SystemSettings extends Output_HTML_Form
{
    public function __construct()
    {
        $config = GConfig::get_instance();
        
        $zone_identifiers = DateTimeZone::listIdentifiers();
        $tzones = array();
        foreach($zone_identifiers as $zone)
            $tzones[$zone] = $zone;
            
        parent::__construct(array(
            'site-title' => array('display' => 'Site title:', 'value' => $config->site->title),
            'site-timezone' => array('display' => 'Timezone:', 'type' => 'dropbox', 'optionlist' => $tzones,
                'value' => $config->site->timezone),
            'email-administrator' => array('display' => "Administrator's mail:", 'value' => $config->email->administrator,
                'hint' => 'The mail that will receive notifications for the site.'),
            'email-sender' => array('display' => "Sender mail address:", 'value' => $config->email->sender,
                'hint' => 'The sender of the site notifications.')
            ),
            array('title' => 'System settings',
                'css' => array('ui-form'),
		        'buttons' => array(
		            'save' => array('display' => 'Save')
                )
            )
        );
    }
    
    public function on_valid($values)
    {
        $config = GConfig::get_writable_copy();
        $config->site->title = $values['site-title'];
        $config->site->timezone = $values['site-timezone'];
        $config->email->administrator = $values['email-administrator'];
        $config->email->sender = $values['email-sender'];
        
        GConfig::update($config);
        CM5_Logger::get_instance()->notice("System settings have been changed.");
        UrlFactory::craft('system.settings')->redirect();
    }
};

?>
