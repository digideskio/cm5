<?php

class UI_UserDelete extends Output_HTML_Form
{
    public function __construct($u)
    {
        $this->user = $u;
        parent::__construct(array(
            'msg' => array('type' => 'custom', 'value' => 'Are you sure? This action is inreversible!')
            ),
            array('title' => "Delete user \"$u->username\"",
                'css' => array('ui-form', 'ui-form-delete'),
		        'buttons' => array(
		            'delete' => array('display' =>'Delete'),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . UrlFactory::craft('user.admin') . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        $this->user->delete();
        
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
