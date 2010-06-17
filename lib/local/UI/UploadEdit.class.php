<?php

class UI_UploadEdit extends Output_HTML_Form
{
    public function __construct($u)
    {
        $this->upload = $u;
        parent::__construct(array(
            'oldfile' => array('type' => 'custom' , 'value' =>
                tag('span class="filename"', $u->filename) .
                tag('span class="size"', html_human_fsize($u->filesize, ''))
            ),
			'file' => array('type' => 'file', 'display' => 'File'),
			'description' => array('display' => 'Description',
			    'hint' => 'Optional description for file', 'type' => 'textarea',
			    'value' => $u->description)
        ),
        array('title' => 'Edit upload',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'upload' => array('display' =>'Save'),
	            'cancel' => array('display' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('upload.admin') . "'")
                )
            )
        );
    }

    public function on_valid($values)
    {
        if ($this->get_field_value('file'))
        {
            // Update file
            $this->upload->update_file($values['file']['data'], $values['file']['orig_name']);
        }
        $this->upload->description = $values['description'];
        $this->upload->save();
        
         UrlFactory::craft('upload.admin')->redirect();
    }
};

?>
