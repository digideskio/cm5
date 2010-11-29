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

class UI_CreatePage extends Output_HTML_Form
{
    public function __construct($parent_id)
    {
        if (($parent_id !== null) && (!Page::open($parent_id)))
            $parent_id = null;

        $this->parent_id = $parent_id;
        parent::__construct(array(
			'title' => array('display' => 'Title', 'value', 'regcheck' => '/^.{3,}$/',
			    'onerror' => 'You must put a title on article'),
			'slug' => array('display' => 'Slug', 'value', 'regcheck' => '/^[\w\-]{1,}$/',
			    'onerror' => 'You must setup a slug for this article'),
			'status' => array('display' => 'Status', 'type' => 'dropbox',
			    'optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ),
			    'value' => 'draft'
			 )
        ),
        array('title' => 'Create page',
            'css' => array('ui-form', 'ui-createpage-form'),
		    'buttons' => array(
		        'create' => array('display' =>'Create'),
	            'cancel' => array('display' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('page.admin') . "'")
                )
            )
        );
    }

    public function on_valid($values)
    {   
        $values['parent_id'] = $this->parent_id;
        $p = Page::create($values);
        UrlFactory::craft('page.edit', $p->id)->redirect();
    }
};

?>
