<?php

//! Class to hold CMS responses
class CMS_Response
{
    protected $headers = array();
    
    public $document = null;

    public $cachable = true;
    
    public function add_header($header)
    {
        $this->headers[] = $header;
    }
    
    public function show()
    {
        foreach($this->headers as $h)
            header($h);
            
        echo $this->document;
    }
}

?>
