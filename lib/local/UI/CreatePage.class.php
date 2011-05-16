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

class UI_CreatePage extends Form_Html
{
    public function __construct($parent_id)
    {
        if (($parent_id !== null) && (!Cm5_Model_Page::open($parent_id)))
            $parent_id = null;
        $this->parent_id = $parent_id;
        parent::__construct(null,
        array('title' => 'Create page',
            'attribs' => array('class' => 'form createpage'),
		    'buttons' => array(
		        'create' => array('label' =>'Create'),
	            'cancel' => array('label' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('page.admin') . "'")
                )
            )
        );
        
        $this->addMany(
        	field_text('title', array('label' => 'Title', 'pattern' => '/^.{3,}$/')),
			field_text('slug', array('label' => 'Slug', 'pattern' => '/^[\w\-]{1,}$/')),
			field_select('status', array('label' => 'Status', 'type' => 'dropbox',
			    'optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ),
			    'value' => 'draft'
			 ))
		);

    }

    public function onProcessValid()
    {   
    	$values = $this->getValues();
        $values['parent_id'] = $this->parent_id;
        $p = CM5_Model_Page::create($values);
        UrlFactory::craft('page.edit', $p->id)->redirect();
    }
};
