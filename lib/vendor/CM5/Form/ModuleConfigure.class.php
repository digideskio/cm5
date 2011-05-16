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
 * Form to configure a module. It has the advantage that
 * builds fields dynamicaly from configuration.
 */
class CM5_Form_ModuleConfigure extends Form_Html
{
	/**
	 * @param CM5_Configurable $module The module to configure
	 */
    public function __construct(CM5_Configurable $module)
    {
        $this->module = $module;
        $this->mconfig = $module->get_config();

        parent::__construct(null,
        array('title' => 'Configure ' . ($this->module->module_type() == 'theme'?'theme':'module') . ': ' . $this->module->info_property('title'),
            'attribs' => array('class' => 'form moduleconfig'),
		    'buttons' => array(
		        'upload' => array('label' =>'Save'),
	            'cancel' => array('label' =>'Cancel', 'type' => 'button',
	                'attribs' => array('onclick' => "window.location='" . UrlFactory::craft('module.admin') . "'"))
                )
            )
        );
        
        // Convert all config options to editable fields
        foreach($module->config_options() as $name => $options) {
        	$options['type'] = !isset($options['type'])?'text':$options['type'];
        	
        	if (in_array($options['type'], array('radio', 'checkbox'))) {
        		$options['value'] = true;
        		$options['checked'] = $this->mconfig->{$name};
        	} else {
        		$options['value'] = $this->mconfig->{$name};
        	}
        	
        	$this->add(call_user_func('field_' . $options['type'], $name, $options));
        }   
        
    }
    
    public function onProcessValid()
    {
    	$values = $this->getValues();
    	
        foreach($values as $id => $value)
            $this->mconfig->{$id} = $value;

        $this->module->save_config();
        
        // Omit redirect not very usefull
        return; 
                
        if ($this->module->module_type() === 'theme')
            UrlFactory::craft('theme.admin')->redirect();
            
        UrlFactory::craft('module.admin')->redirect();
    }
};
