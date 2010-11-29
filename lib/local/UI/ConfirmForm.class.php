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

class UI_ConfirmForm extends Output_HTML_Form
{
    public function __construct($title, $message, $ok_button, $ok_action, $ok_action_args, $cancel_url)
    {
        $this->ok_action = $ok_action;
        $this->ok_action_args = $ok_action_args;
        
        parent::__construct(array(
            'msg' => array('type' => 'custom', 'value' => $message)
            ),
            array('title' => $title,
                'css' => array('ui-form', 'ui-form-confirm'),
		        'buttons' => array(
		            'delete' => array('display' => $ok_button),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . $cancel_url . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        call_user_func_array($this->ok_action, $this->ok_action_args);
    }
};

?>
