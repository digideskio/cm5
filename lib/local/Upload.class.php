<?php


class ImageUpload extends DB_Record
{
    static public $table = 'image_uploads';

    static public $fields = array(
        'upload_id' => array('pk' => true, 'fk' => 'Upload'),
        'width',
        'height',
        );
}
        
class Upload extends DB_Record
{
    static public $table = 'uploads';

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'filename',
        'filesize',
        'store_file',
        'mime',
        'description'
        );
    
    private function update_image_info()
    {
        if ($info = $this->image_info())
            $info->delete();
            
        // Check if it is image
        if (($info = getimagesize(Config::get('site.upload_folder') .'/' . $this->store_file)) === false)
            return;
            
        if (! in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
            return;

        // Add image record
        $img = ImageUpload::create(array(
            'upload_id' => $this->id,
            'width' => $info[0],
            'height' => $info[1]
        ));
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
            'mime' => $mime_type
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
    
    function image_info()
    {
        return ImageUpload::open($this->id);
    }
}

Upload::events()->connect('op.pre.delete', function($e){
    // Delete image if any
    $r = $e->arguments['record'];
    if ($i = $r->image_info())
        $i->delete();
        
    // delete file from file system
    unlink(Config::get('site.upload_folder') . '/' . $r->store_file);
});
?>
