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
 * Slug field
 */
class CM5_Form_PageEdit_Field_Slug extends Form_Field_Input
{
	public function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	public function render($options)
	{
		$fullurl = explode('/', (string)UrlFactory::craft_fqn('page.view', $this->options['page']));
        $url = implode('/', array_slice($fullurl, 0, -1)) . '/';
		return tag('table',
			tag('tr',
				tag('td', $url)->attr('style', 'white-space: pre;'),
				tag('td', parent::renderInput($options)))
		);
	}
}

/**
 * Form for editing an existing page.
 */
class CM5_Form_PageEdit extends Form_Html
{
	/**
	 * Page that we are editting;
	 * @var CM5_Model_Page
	 */
	private $page;
	
	/**
	 * Construct a new editing form
	 * @param CM5_Model_Page $page The page that will be edited
	 */
    public function __construct(CM5_Model_Page $page)
    {
    	// Save page
        $this->page = $page;
        
		parent::__construct(null, array(
			'title' => 'Edit page',
            'action' => UrlFactory::craft('page.editform', $this->page->id),
            'attribs' => array('class' => 'form page-editor'),
		    'buttons' => array(
		    	'save' => array('label' =>'Save')
			)
        ));
    }
    
    public function configure() {    
        $this->addMany(
        	field_text('title', array('label' => '', 'value' => $this->page->title)),
        	new CM5_Form_PageEdit_Field_Slug('slug', array(
        		'label' => '',
        		'page' => $this->page,
        		'value' => $this->page->slug,
        		'pattern' => '/^[\w\-]{1,}$/')),
        	field_select('status', array('label' => 'Status','optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ), 'value' => $this->page->status)),
			field_raw('preview', array('label' => '', 'escape' => false,
				'value' => '<a class="view button" href="' . 
					(string)UrlFactory::craft_fqn('page.view', $this->page) .
					'" target="_blank"><span class="download">View</span></a>')),
			field_textarea('body', array(
				'label' => '',
				'multiline'=> true,
				'value' => $this->page->body,
			    'attribs' => array('id' => 'bodyeditor')
				))
		);
        
		if ($this->page->system) {
            unset($this->fields['slug'], $this->fields['title'], $this->fields['status']);
            $this->options['title'] = "Edit \"{$this->page->title}\" page";
        }
    }
    
    /**
     * Get the editing page.
     * @return CM5_Model_Page 
     */
    public function getPage()
    {
    	return $this->page;
    }
    
    /**
     * Process field
     */
    public function onProcessValid()
    {
    	$values = $this->getValues();
	   	foreach($values as $name => $value) {
    		if (in_array($name, array('preview')))
        		continue;
			$this->page->{$name} = $value;
		}
    	$this->page->save();
    }
};

