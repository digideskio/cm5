<?php

class UI_UploadDelete extends Output_HTML_Form
{
    public function __construct($u)
    {
        $this->upload = $u;
        parent::__construct(array(
            'msg' => array('type' => 'custom', 'value' => 'Are you sure? This action is inreversible!')
            ),
            array('title' => "Delete \"$u->filename\"",
                'css' => array('ui-form', 'ui-form-delete'),
		        'buttons' => array(
		            'delete' => array('display' =>'Delete'),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . UrlFactory::craft('upload.admin') . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        $this->upload->delete();
        
        UrlFactory::craft('upload.admin')->redirect();
    }
};

?>
