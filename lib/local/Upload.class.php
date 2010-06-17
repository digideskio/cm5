<?php

class Upload extends DB_Record
{
    static public $table = 'uploads';

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'filename',
        'filesize',
        'store_file',
        'mime',
        'uploader',
        'lastupdated' => array('type' => 'datetime'),
        'description',
        'is_image' => array('default' => false),
        'image_width',
        'image_height'
        );
    
    static public $thumb_cache = null;
    private function update_image_info()
    {
        $this->is_image = false;
        $this->image_width = null;
        $this->image_height = null;
        $this->save();
        // Clear thumb image cache
        if (self::$thumb_cache)
            self::$thumb_cache->delete($this->id);
            
        // Check if it is image
        if (($info = getimagesize(Config::get('site.upload_folder') .'/' . $this->store_file)) === false)
            return;
            
        if (! in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
            return;


        // Add image record
        $this->is_image = true;
        $this->image_width = $info[0];
        $this->image_height = $info[1];
        $this->save();
    }
    
    static function from_file($data, $filename)
    {   
        $upload_folder = Config::get('site.upload_folder');
        
        // Get mime type
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($data);
                
        if (preg_match_all('/^\s*(?P<mime_type>[^;\s]+)/', $mime, $matches))
            $mime_type = $matches['mime_type'][0];
        else
            $mime_type = $mime;

        // Calculate save_path
        $path_count = 0;
        $store_file =  md5($data) . '.dat';

        while(file_exists($upload_folder . '/' . $store_file))
            $store_file = '/' . md5($data) . '.' . ($path_count += 1) . '.dat';

        // Save data
        file_put_contents($upload_folder . '/' . $store_file, $data);

        // Save entry
        $a = Upload::create(array(
            'filename' => $filename,
            'filesize' => strlen($data),
            'store_file' => $store_file,
            'mime' => $mime_type,
            'lastupdated' => new DateTime()
        ));
        
        // Check if it is image
        $a->update_image_info();
        
        return $a;
    }
    
    public function update_file($data, $filename)
    {
        $upload_folder = Config::get('site.upload_folder');
        
        // Get mime type
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($data);
                
        if (preg_match_all('/^\s*(?P<mime_type>[^;\s]+)/', $mime, $matches))
            $mime_type = $matches['mime_type'][0];
        else
            $mime_type = $mime;
            
        // Overwrite old file
        file_put_contents($upload_folder . '/' . $this->store_file, $data);
        
        // Save to database
        $this->filesize = strlen($data);
        $this->filename = $filename;
        $this->lastupdated = new DateTime();
        $this->mime = $mime_type;
        $this->save();
        
        // Update image information
        $this->update_image_info();
    }
    
    function dump_file()
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
        echo file_get_contents(Config::get('site.upload_folder') . '/' . $this->store_file);
    }
    
    function dump_thumb()
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
        $img = new Image(Config::get('site.upload_folder') . '/' . $this->store_file);
        $thumb = $img->resize(80,80)->data(array('quality' => '91', 'format' => IMAGETYPE_PNG));
        
        if (self::$thumb_cache)
            self::$thumb_cache->set($this->id, $thumb);
            
        header('Content-Type: image/png');
        echo $thumb;
    }
}

Upload::$thumb_cache = new Cache_File(Config::get('site.thumbs_folder'));
Upload::events()->connect('op.pre.delete', function($e){

    $r = $e->arguments['record'];

    // Clear thumb image cache
    if (Upload::$thumb_cache)
        Upload::$thumb_cache->delete($r->id);
    
    // delete file from file system
    unlink(Config::get('site.upload_folder') . '/' . $r->store_file);
});
?>
