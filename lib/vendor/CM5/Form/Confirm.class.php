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
 * Form to simply confirm an action
 */
class CM5_Form_Confirm extends Form_Html
{
	/**
	 * @param string $title Title of the confiramtion dialog
	 * @param string $message Message to confirm
	 * @param string $ok_button Title of OK button
	 * @param callable $ok_action Action to execute on ok.
	 * @param array $ok_action_args Extra arguments to pass at ok action.
	 * @param string $cancel_url The cancel url to visit on cancel.
	 */
    public function __construct($title, $message, $ok_button, $ok_action, $ok_action_args, $cancel_url)
    {
        $this->ok_action = $ok_action;
        $this->ok_action_args = $ok_action_args;
        
        parent::__construct(null,
            array('title' => $title,
                'attribs' => array('class' => 'form form-confirm'),
		        'buttons' => array(
		            'delete' => array('label' => $ok_button),
		            'cancel' => array('label' =>'Cancel', 'type' => 'button',
		                'attribs' => array('onclick' => "window.location='" . $cancel_url . "'"))
                    )
                )
        );
        
        $this->add(field_raw('msg', array('label' => '', 'value' => $message)));
    }
    
    public function onProcessValid()
    {
        call_user_func_array($this->ok_action, $this->ok_action_args);
    }
};
