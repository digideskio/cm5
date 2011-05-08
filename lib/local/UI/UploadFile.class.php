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

class UI_UploadFile extends Form_Html
{
    public function __construct()
    {
        parent::__construct(array(
        	'title' => 'Upload a new file',
            'attribs' => array('class' => 'ui-form ui-form-upload'),
		    'buttons' => array(
		        'upload' => array('label' =>'Upload'),
	            'cancel' => array('label' =>'Cancel', 'type' => 'button',
	                'attribs' => array('onclick' => "window.location='" . UrlFactory::craft('upload.admin') . "'")
        		)
            )
        ));
        
        $this->addFields(
        	//'file' => array('type' => 'file', 'display' => 'File'),
			new Form_Field_Text('description', array(
				'multiline' => true,
				'label' => 'Description',
				'hint' => 'Optional description for file',
				'validator' => Form_Validator::valid()))
        );
    }

    public function onProcessPost()
    {
       /* if (!($file = $this->get_field_value('file')))
            $this->getField('file')->invalidate('You must select a file to upload');
        
        if (count(Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($file['orig_name'])))
            $this->getField('file')->invalidate('There is already an upload with the same filename.');
            */
    }
    
    public function onProcessValid()
    {
    	return;
        $up = Upload::from_file($values['file']['data'], $values['file']['orig_name']);
        $up->description = $values['description'];
        $up->save();
        
        if (!$up)
            $this->invalidate_field('file', 'There was an unknown problem trying to upload file');
        else
            UrlFactory::craft('upload.admin')->redirect();
    }
};
