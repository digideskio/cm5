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

/**
 * Form to edit system settings
 */
class CM5_Form_SystemSettings extends Form_Html
{
    public function __construct()
    {
        parent::__construct(null,
            array('title' => 'System settings',
                'attribs' => array('class' => 'form'),
		        'buttons' => array(
		            'save' => array('label' => 'Save')
                )
            )
        );
    }
    
    public function configure()
    {
    	$config = CM5_Config::getInstance();
    	$zone_identifiers = DateTimeZone::listIdentifiers();
        $tzones = array();
        foreach($zone_identifiers as $zone)
            $tzones[$zone] = $zone;
            
		$this->addMany(
        	field_set('site', array('label' => 'General'))->addMany(
        		field_text('title', array('label' => 'Site title:', 'value' => $config->site->title)),
        		field_select('timezone', array('label' => 'Timezone:', 'optionlist' => $tzones,
                'value' => $config->site->timezone))
        	),
        	field_set('email', array('label' => 'Email notifications.'))->addMany(
            	field_email('administrator', array('label' => "Administrator's mail:", 'value' => $config->email->administrator,
                'hint' => 'The mail that will receive notifications for the site.')),
            	field_email('sender', array('label' => "Sender mail address:", 'value' => $config->email->sender,
                	'hint' => 'The sender of the site notifications.')),
            	field_set('transport', array('label' => 'Transport'))->addMany(
            		field_select('protocol', array('label' => 'Protocol to be used', 'value' => $config->email->transport->protocol,
            			'optionlist' => array(
            			'sendmail' => 'Sendmail',
            			'smtp' => 'SMTP'
            		))),
            		field_text('host', array('label' => 'Host to send mails at.', 'hint' => 'Needed by smtp.',
            			'value' => $config->email->transport->host)),
            		field_number('port', array('label' => 'Port to connect at.', 'hint' => 'Leave it empty for default.',
            			'min' => 0, 'max' => 65535, 'step' => 1, 'value' => $config->email->transport->port)),
            		field_select('ssl', array('label' => 'Secure connection', 'value' => $config->email->transport->ssl,
            			'optionlist' => array(
            			'' => 'None',
            			'ssl' => 'SSL',
            			'tls' => 'TLS'
            		))),
            		field_select('auth', array('label' => 'Authentication', 'value' => $config->email->transport->auth,
            			'optionlist' => array(
            			'plain' => 'Plain',
            			'login' => 'Login',
            			'crammd5' => 'Cram-MD5'
            		))),
            		field_text('username', array('label' => 'Username', 'value' => $config->email->transport->username)),
            		field_text('password', array('label' => 'Password', 'value' => $config->email->transport->password))
            	)
            )
        );
    }
    
    public function onProcessValid()
    {
    	$values = $this->getValues();
    	
        $config = CM5_Config::getWritableCopy();
        foreach($values['site'] as $k => $v)
        	$config->site->{$k} = $v;
       	$config->email = $values['email'];
        
        CM5_Config::update($config);
        CM5_Logger::getInstance()->notice("System settings have been changed.");
        UrlFactory::craft('system.settings')->redirect();
    }
};
