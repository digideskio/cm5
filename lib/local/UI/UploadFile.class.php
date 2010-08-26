<?php

class UI_UploadFile extends Output_HTML_Form
{
    public function __construct()
    {
        parent::__construct(array(
			'file' => array('type' => 'file', 'display' => 'File'),
			'description' => array('display' => 'Description', 'hint' => 'Optional description for file', 'type' => 'textarea')
        ),
        array('title' => 'Upload a new file',
            'css' => array('ui-form', 'ui-form-upload'),
		    'buttons' => array(
		        'upload' => array('display' =>'Upload'),
	            'cancel' => array('display' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('upload.admin') . "'")
                )
            )
        );
    }

    public function on_post()
    {
        if (!($file = $this->get_field_value('file')))
            $this->invalidate_field('file', 'You must select a file to upload');
        
        if (count(Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($file['orig_name'])))
            $this->invalidate_field('file', 'There is already an upload with the same filename.');
    }
    
    public function on_valid($values)
    {
        $up = Upload::from_file($values['file']['data'], $values['file']['orig_name']);
        $up->description = $values['description'];
        $up->save();
        
        if (!$up)
            $this->invalidate_field('file', 'There was an unknown problem trying to upload file');
        else
            UrlFactory::craft('upload.admin')->redirect();
    }
};

?>
