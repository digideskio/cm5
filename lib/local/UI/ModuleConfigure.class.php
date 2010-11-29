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

class UI_ModuleConfigure extends Output_HTML_Form
{
    public function __construct($module)
    {
        $this->module = $module;
        $this->mconfig = $module->get_config();

        $fields = array();
        foreach($module->config_options() as $id => $opt)
        {
            $fields[$id] = array('display' => $opt['display']);
            $f =  & $fields[$id];
            if (isset($opt['type']))
            {
                if ($opt['type'] === 'checkbox')
                    $f['type'] = 'checkbox';
                if ($opt['type'] === 'select')
                {
                    $f['type'] = 'dropbox';
                    $f['optionlist'] = $opt['options'];
                }
                if ($opt['type'] === 'textarea')
                    $f['type'] = 'textarea';
                if ($opt['type'] === 'color')
                    $f['htmlattribs'] = array('class' => 'color');

            }
            $f['value'] = $this->mconfig->{$id};
        }   
        parent::__construct(
            $fields,
        array('title' => 'Configure ' . ($this->module->module_type() == 'theme'?'theme':'module') . ': ' . $this->module->info_property('title'),
            'css' => array('ui-form', 'ui-form-moduleconfig'),
		    'buttons' => array(
		        'upload' => array('display' =>'Save'),
	            'cancel' => array('display' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('module.admin') . "'")
                )
            )
        );
    }
    
    public function on_valid($values)
    {
        foreach($values as $id => $value)
            $this->mconfig->{$id} = $value;

        $this->module->save_config();

        return; // Omit redirect not very usefull        
        if ($this->module->module_type() === 'theme')
            UrlFactory::craft('theme.admin')->redirect();
            
        UrlFactory::craft('module.admin')->redirect();
    }
};

?>
