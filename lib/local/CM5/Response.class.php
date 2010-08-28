<?php

//! Class to hold CMS responses
class CM5_Response
{
    //! Extra headers to be set with response
    protected $headers = array();

    //! The HTMLDoc of this response object    
    public $document = null;

    //! A flag if this response should be cached
    public $cachable = true;
    
    //! Function to add header in response
    public function add_header($header)
    {
        $this->headers[] = $header;
    }
    
    //! Dump this reponse object in output
    public function show()
    {
        foreach($this->headers as $h)
            header($h);
            
        echo $this->document;
    }
}

?>
