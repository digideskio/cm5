<?php

class UI_CreatePage extends Output_HTML_Form
{
    public function __construct($parent_id)
    {
        $this->parent_id = $parent_id;
        parent::__construct(array(
			'title' => array('display' => 'Title', 'value'),
			'slug' => array('display' => 'Slug', 'value'),
			'status' => array('display' => 'Status', 'type' => 'dropbox',
			    'optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ),
			    'value' => 'draft'
			 )
        ),
        array('title' => 'Edit page',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'create' => array('display' =>'Create')
                )
            )
        );
    }

    public function on_valid($values)
    {   
        $values['parent_id'] = $this->parent_id;    
        $p = Page::create($values);
        UrlFactory::craft('page.edit', $p)->redirect();
    }
};

?>
