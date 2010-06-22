<?php

class UI_EditPage extends Output_HTML_Form
{
    public function __construct($page)
    {
        $this->page = $page;
        
        $title = 'Edit page';
        $fields = array(
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
        );
        
        if ($page->system)
        {
            unset($fields['slug'], $fields['title'], $fields['status']);
            $title = "Edit \"{$page->title}\" page";
        }
        
        parent::__construct($fields,
            array('title' => $title,
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
