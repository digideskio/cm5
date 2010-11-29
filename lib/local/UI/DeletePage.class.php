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

class UI_DeletePage extends Output_HTML_Form
{
    public function __construct($p)
    {
        $this->page = $p;
        parent::__construct(array(
                'mode' => array('type' => 'radio', 'mustselect' => true, 'display' => 'Action to take in subpages',
                    'onerror' => 'You must select deletion mode.',
                    'optionlist' => array(
                        'delete-all' => 'Delete this page and all subpages',
                        'move-to-parent' => 'Move all subchilds to parent'
                    )
                ),
                'msg' => array('type' => 'custom', 'value' => 'Are you sure? This action is inreversible!')
            ),
            array(
                'title' => "Delete \"{$p->title}\"",
                'css' => array('ui-form', 'ui-form-delete'),
		        'buttons' => array(
		            'delete' => array('display' =>'Delete'),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . UrlFactory::craft('page.admin') . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        if ($values['mode'] == 'delete-all')
        {
            $this->page->delete_all();
        }
        else if ($values['mode'] == 'move-to-parent')
        {
            $this->page->delete_move_orphans();
        }
        UrlFactory::craft('page.admin')->redirect();
    }
};

?>
