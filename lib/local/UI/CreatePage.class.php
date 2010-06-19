<?php

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
            'css' => array('ui-form', 'ui-page-form'),
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
        UrlFactory::craft('page.edit', $p)->redirect();
    }
};

?>
