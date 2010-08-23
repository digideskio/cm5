<?php

class UI_LogClear extends Output_HTML_Form
{
    public function __construct()
    {
        parent::__construct(array(
            'msg' => array('type' => 'custom', 'value' => 'Are you sure? This action is inreversible!')
            ),
            array('title' => "Clear system log",
                'css' => array('ui-form', 'ui-form-delete'),
		        'buttons' => array(
		            'delete' => array('display' =>'Delete'),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . UrlFactory::craft('log.view') . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        Log::reset();        
        UrlFactory::craft('log.view')->redirect();
    }
};

?>
