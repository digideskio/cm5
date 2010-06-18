<?php

class UI_EditPage extends Output_HTML_Form
{
    public function __construct($page)
    {
        $this->page = $page;
        
        parent::__construct(array(
			'title' => array('display' => 'Title', 'value' => $page->title),
			'slug' => array('display' => 'Slug', 'value' => $page->slug, 'regcheck' => '/^[\w\-]{1,}$/',
			    'onerror' => 'You must setup a slug for this article'),
			'status' => array('display' => 'Status', 'type' => 'dropbox',
			    'optionlist' => array(
			        'published' => 'Published',
			        'draft' => 'Draft'
			    ),
			    'value' => $this->page->status
			 ),
			'body' => array('display' => '', 'type'=> 'textarea', 'value' => $page->body,
			    'htmlattribs' => array('id' => 'bodyeditor'))
        ),
        array('title' => 'Edit page',
            'css' => array('ui-form', 'ui-page-form'),
		    'buttons' => array(
		        'save' => array('display' =>'Save')
                )
            )
        );
    }

    public function on_valid($values)
    {
        foreach($values as $name => $v)
            $this->page->{$name} = $v;
        $this->page->save();
    }
};

?>
