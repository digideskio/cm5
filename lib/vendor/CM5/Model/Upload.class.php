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
 * Model class for uploads table.
 * 
 * @author sque@0x0lab.org
 *
 * @property integer $id
 * @property string $filename
 * @property integer $filesize
 * @property string $store_file
 * @property string $sha1_sum
 * @property string $mime
 * @property string $uploader
 * @property string $description 
 * @property DateTime $lastmodified
 * @property boolean $is_image
 * @property integer $image_width
 * @property integer $image_height
 */
class CM5_Model_Upload extends DB_Record
{
    static public function get_table()
    {   
        return CM5_Config::getInstance()->db->prefix . 'uploads';
    }

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'filename',
        'filesize',
        'store_file',
        'sha1_sum',
        'mime',
        'uploader',
        'lastmodified' => array('type' => 'datetime'),
        'description',
        'is_image' => array('default' => false),
        'image_width',
        'image_height'
        );
    
    static public $thumb_cache = null;
    
    /**
     * Update image metadata
     */
    private function updateImageInfo()
    {
        $this->is_image = false;
        $this->image_width = null;
        $this->image_height = null;
        $this->save();
        
        // Clear thumb image cache
        if (self::$thumb_cache)
            self::$thumb_cache->delete($this->id);
            
        // Check if it is image
        if (($info = getimagesize(CM5_Config::getInstance()->site->upload_folder .'/' . $this->store_file)) === false)
            return;
            
        if (! in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
            return;

        // Add image record
        $this->is_image = true;
        $this->image_width = $info[0];
        $this->image_height = $info[1];
        $this->save();
    }
    
    /**
     * Helper function to extract mime from data
     * @param string $data
     */
    private static function getMimeFromData($data)
    {
        $fname = tempnam(sys_get_temp_dir(), 'dd');
        file_put_contents($fname, $data);
        
        $mime_type = mime_content_type($fname);
        unlink($fname);
        return $mime_type;
        /*
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($data);
        
                
        if (preg_match_all('/^\s*(?P<mime_type>[^;\s]+)/', $mime, $matches))
            $mime_type = $matches['mime_type'][0];
        else
            $mime_type = $mime;
        return $mime_type;*/
    }
    
    /**
     * Construct an upload from data
     * @param UploadedFile $upload
     */
    static function createFromData($data, $filename)
    {   
        $upload_folder = CM5_Config::getInstance()->site->upload_folder;
       
        // Calculate save_path
        $path_count = 0;
        $datasum = sha1($data);
        $store_file = $datasum . '.dat';
        
        // Get mime type
        $mime_type = self::getMimeFromData($data);

        while(file_exists($upload_folder . '/' . $store_file))
            $store_file = '/' . $datasum . '.' . ($path_count += 1) . '.dat';

        // Save data
        file_put_contents($upload_folder . '/' . $store_file, $data);      

        // Save entry
        $up = CM5_Model_Upload::create(array(
            'filename' => $filename,
            'filesize' => strlen($data),
            'store_file' => $store_file,
            'sha1_sum' => $datasum,
            'mime' => $mime_type,
            'lastmodified' => new DateTime()
        ));
        
        // Check if it is image
        $up->updateImageInfo();
        
        return $up;
    }
    
    /**
     * Construct an upload from UploadedFile
     * @param UploadedFile $upload
     */
    static function createFromUploaded(UploadedFile $upload)
    {   
        $upload_folder = CM5_Config::getInstance()->site->upload_folder;
       
        // Calculate save_path
        $path_count = 0;
        $data = file_get_contents($upload->getTempName());
        $datasum = sha1($data);
        $store_file = $datasum . '.dat';
        
        // Get mime type
        $mime_type = self::getMimeFromData($data);

        while(file_exists($upload_folder . '/' . $store_file))
            $store_file = '/' . $datasum . '.' . ($path_count += 1) . '.dat';

        // Save data
        $upload->move($upload_folder . '/' . $store_file);      

        // Save entry
        $up = CM5_Model_Upload::create(array(
            'filename' => $upload->getName(),
            'filesize' => strlen($data),
            'store_file' => $store_file,
            'sha1_sum' => $datasum,
            'mime' => $mime_type,
            'lastmodified' => new DateTime()
        ));
        
        // Check if it is image
        $up->updateImageInfo();
        
        return $up;
    }
    
    /**
     * Update data from UploadedFile
     * @param UploadedFile $upload
     */
    public function updateFromUploaded(UploadedFile $upload)
    {
        $upload_folder = CM5_Config::getInstance()->site->upload_folder;
        
        // Get mime type
        $data = file_get_contents($upload->getTempname());
        $mime_type = self::getMimeFromData($data);
            
        // Overwrite old file
        unlink($this->getStoragePath());
        $upload->move($this->getStoragePath());        
        
        // Save to database
        $this->filesize = strlen($data);
        $this->sha1_sum = sha1($data);
        $this->lastmodified = new DateTime();
        $this->mime = $mime_type;
        $this->save();
        
        // Update image information
        $this->updateImageInfo();
    }
    /**
     * Get the path where actual file is store
     */
    function getStoragePath()
    {
    	return CM5_Config::getInstance()->site->upload_folder . '/' . $this->store_file;
    }
    
    /**
     * Get the data of this file
     */
    function getData()
    {
        return file_get_contents($this->getStoragePath());
    }
    
    /**
     * Dump file to output;
     */
    function dumpFile()
    {   
        $dispo = 'inline';    

        if (substr($this->mime, 0, 4) == 'text')
            // All text/* translate to text/plain (for security)
            header('Content-Type: ' . 'text/plain');
        else if (substr($this->mime, 0, 5) == 'image')
            // Images are served as is
            header('Content-Type: ' . $this->mime);
        else
            // The rest are served as attachments
            $dispo = 'attachment';
            
        header("Content-Disposition: {$dispo}; filename={$this->filename}");
        echo $this->getData();
    }
    

    /**
     * Dump the thumbnail of this upload
     */
    function dumpThumb()
    {
        if (!$this->is_image)
            return;

        // Check cache
        if (self::$thumb_cache)
        {
            $thumb = self::$thumb_cache->get($this->id, $succ);
            if ($succ)
            {
                header('Content-Type: image/png');
                echo $thumb;
                exit;
            }
        }
        $img = new Image(CM5_Config::getInstance()->site->upload_folder . '/' . $this->store_file);
        $thumb = $img->resize(80,80)->data(array('quality' => '91', 'format' => IMAGETYPE_PNG));
        
        if (self::$thumb_cache)
            self::$thumb_cache->set($this->id, $thumb);
            
        header('Content-Type: image/png');
        echo $thumb;
    }
}

CM5_Model_Upload::$thumb_cache = new Cache_File(CM5_Config::getInstance()->site->cache_folder, 'thumb_');
CM5_Model_Upload::events()->connect('op.pre.delete', function($e) {

    $r = $e->arguments["record"];

    // Clear thumb image cache
    if (CM5_Model_Upload::$thumb_cache)
        CM5_Model_Upload::$thumb_cache->delete($r->id);
    
    // delete file from file system
    unlink($r->getStoragePath());
});

CM5_Model_Upload::events()->connect('op.pre.create', function($e) {
    if (!isset($e->filtered_value["uploader"]))
        $e->filtered_value["uploader"] = Authn_Realm::get_identity()->id();
});

CM5_Model_Upload::events()->connect('op.post.create', function($e) {
    $u = $e->arguments["record"];

    // Log event
    CM5_Logger::getInstance()->info("File \"{$u->filename}\" was uploaded.");
});

CM5_Model_Upload::events()->connect('op.pre.delete', function($e) {
    $u = $e->arguments["record"];

    // Log event
    CM5_Logger::getInstance()->notice("File \"{$u->filename}\" was deleted.");
});

CM5_Model_Upload::events()->connect('op.post.save', function($e) {
    $u = $e->arguments["record"];

    // Log event
    CM5_Logger::getInstance()->info("File \"{$u->filename}\" was changed.");
});

