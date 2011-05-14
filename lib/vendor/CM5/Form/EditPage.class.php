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
 * Form for editing an existing page.
 * @author sque
 *
 */
class CM5_Form_EditPage extends CM5_Form_Event
{
	/**
	 * Construct a new editing form
	 * @param CM5_Model_Page $page The page that will be edited
	 */
    public function __construct(CM5_Model_Page $page)
    {
        $this->page = $page;
        
        $title = 'Edit page';
        $fields = array(
			'title' => array('display' => '', 'value' => $page->title),
			'slug' => array('display' => '', 'value' => $page->slug, 'regcheck' => '/^[\w\-]{1,}$/',
			    'onerror' => 'You must setup a slug for this article'),
			'status' => array('display' => 'Status', 'type' => 'dropbox',
			    'optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ),
			    'value' => $this->page->status
			 ),
			'preview' => array('display' => '', 'type' => 'custom',
			    'value' => ''),
			'body' => array('display' => '', 'type'=> 'textarea', 'value' => $page->body,
			    'htmlattribs' => array('id' => 'bodyeditor'))
        );
        
        if ($page->system) {
            unset($fields['slug'], $fields['title'], $fields['status']);
            $title = "Edit \"{$page->title}\" page";
        }
        
        parent::__construct(
        	$fields,
            array('title' => $title,
                'css' => array('ui-form', 'ui-page-form'),
		        'buttons' => array(
		            'save' => array('display' =>'Save')
                )
            )
        );
        
        $this->fields['preview']['value'] =  '<a class="view button" href="' . (string)UrlFactory::craft_fqn('page.view', $this->page)
            . '" target="_blank"><span class="download">View</span></a>';
    }
    
    public function on_postrender($div)
    {
    	// Fix action
    	$div->childs[0]->attr('action', UrlFactory::craft('page.editform', $this->page->id));
    	
        if ($this->page->system) {
        	parent::on_postrender($div);
        	return;
        }
            
        $fullurl = explode('/', (string)UrlFactory::craft_fqn('page.view', $this->page));
        $url = implode('/', array_slice($fullurl, 0, -1)) . '/';
        $dt = $div->childs[0]->childs[3];
        $dt->childs[2] = $dt->childs[1];
        $dt->childs[1] = $url;
        
        parent::on_postrender($div);
    }

    public function on_valid($values)
    {
        foreach($values as $name => $v) {
            if (in_array($name, array('preview')))
                continue;
            $this->page->{$name} = $v;
        }
        $this->page->save();
    }
};