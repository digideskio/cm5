<?php

class UI_ConfirmForm extends Output_HTML_Form
{
    public function __construct($title, $message, $ok_button, $ok_action, $ok_action_args, $cancel_url)
    {
        $this->ok_action = $ok_action;
        $this->ok_action_args = $ok_action_args;
        
        parent::__construct(array(
            'msg' => array('type' => 'custom', 'value' => $message)
            ),
            array('title' => $title,
                'css' => array('ui-form', 'ui-form-confirm'),
		        'buttons' => array(
		            'delete' => array('display' => $ok_button),
		            'cancel' => array('display' =>'Cancel', 'type' => 'button',
		                'onclick' => "window.location='" . $cancel_url . "'")
                    )
                )
        );
    }
    
    public function on_valid($values)
    {
        call_user_func_array($this->ok_action, $this->ok_action_args);
    }
};

?>
