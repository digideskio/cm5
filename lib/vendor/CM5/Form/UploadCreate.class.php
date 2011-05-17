<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */

/**
 * Form to create a new upload
 */
class CM5_Form_UploadCreate extends Form_Html
{
    public function __construct()
    {
        parent::__construct(null, array(
        	'title' => 'Upload a new file',
            'attribs' => array('class' => 'form upload'),
		    'buttons' => array(
		        'upload' => array('label' =>'Upload'),
	            'cancel' => array('label' =>'Cancel', 'type' => 'button',
	                'attribs' => array('onclick' => "window.location='" . UrlFactory::craft('upload.admin') . "'")
        		)
            )
        ));
    }
    
    public function configure()
    {
		$this->addMany(
        	field_file('file', array('label' => 'File', 'multiple' => true, 'required' => true)),
        	field_textarea('description', array('label' => 'Description',
				'hint' => 'Optional description for file',
				'required' => false))
        );
    }

    public function onProcessPost()
    {
       
    	foreach($this->get('file')->getValue() as $file) {
	        if (count(CM5_Model_Upload::raw_query()->select(array('id'))
	        	->where('filename = ?')->execute($file->getName())))
    	        $this->get('file')->invalidate("There is already an upload with the same \"{$file->getName()}\" filename.");
    	}

    }
    
    public function onProcessValid()
    {
    	foreach($this->get('file')->getValue() as $upload) {
	        $up = CM5_Model_Upload::create_from_upload($upload);
	        $up->description = $this->get('description')->getValue();
	        $up->save();
	        
	        if (!$up) {
	            $this->invalidate_field('file', 'There was an unknown problem trying to upload file');
	            return;
	        }
    	}
        
        UrlFactory::craft('upload.admin')->redirect();
    }
};
