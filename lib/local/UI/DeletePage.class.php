<?php

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
