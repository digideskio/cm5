<?php

class UI_CreatePage extends Output_HTML_Form
{
    public function __construct()
    {
       
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
        $p = Page::create($values);
        UrlFactory::craft('page.edit', $p)->redirect();
    }
};

?>
