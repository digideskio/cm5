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
            'css' => array('ui-form', 'ui-form-upload-edit'),
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
