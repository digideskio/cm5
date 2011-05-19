<?php



class CM5_Module_Migration_FixLinks extends Form_Html
{
    public function __construct()
    {
        parent::__construct(null, array(
                'title' => 'Fix internal links',
        		'attribs' => array('class' => 'form'),
                'buttons' => array(
                    'Fix' => array('type' => 'submit')
                )
            )
        );
    }
    
    public function onInitialized()
    {
    	$this->addMany(
            field_raw('description', array('hint' =>
	                'Sometimes after migration links pointing to uploads are broken.
	    			This may happen if you changed domain and you used absolute urls, or
	    			because of moving site to a new subfolder. This tool will search for this links
	    			and update them so they point again at the right resources.',
            		'value' => '')),
            field_text('url-base', array('label' => 'The url base of old links.', 'hint' =>
            		'This is an optional base to make algorithm more acurate. You usually put the base
            		of the old site before migration for example http://old-host/here-is-cms or relative
            		url if you want to fix relative urls /here-is-cms')),
            field_checkbox('write-changes', array('label' => 'Write changes to database.',
            	'hint' => 'By default fix links works in preview mode if you want to actually make this changes
            		check this box.')),
            field_checkbox('user-validation', array('label' => 'Fixing links requires searching and editing each page.
                	Although the algorithm is sophisticated to increase accuracy there is a possibility that some
                	changes may be wrong. Before continuing you should backup your site.
                	Are you sure you want to continue?'))
            );
    }
    
    
    public function onProcessPost()
    {
    	if (!$this->get('user-validation')->isChecked())
    		$this->get('user-validation')->invalidate('You must read and understand the risk before continuing!');
    }
    
    public function __fix_links_callback($matches)
    {
    	$logentry = array('orig-url' => $matches[0],
    		'orig-fname' => $matches[4]);
    	
    	// Base check
    	if ($this->oldurlbase)
    	{
    		$checkbase = substr($matches[3], strlen($matches[3])- strlen($this->oldurlbase));
    		if ($this->oldurlbase != $checkbase)
    			return $matches[0];	// No match    		
    	}
    	
    	// Validate file
		$f = CM5_Model_Upload::open_query()->where("filename = ?")->execute($matches[4]);
		if (!count($f))
			return $matches[0];	// No change
		
		$newurl = $matches[1] . (string)UrlFactory::craft("upload.view", $f[0]);
		
		// Check if it is actual different
		if ($newurl == $matches[0])
			return $matches[0];
			
		$logentry['new-url'] = $newurl;	
		$this->fixed[] = $logentry;
		return $newurl;
    }
    
    public function onProcessValid()
    {    	
    	$values = $this->getValues();
    	$this->fixed = array();
    	$this->oldurlbase = $values['url-base'];
    	
        foreach(CM5_Model_Page::open_all() as $p)
    	{    	   			
			$changed = preg_replace_callback(
		        '#(?P<before>[^\w:\-/\.]|^)((?P<base>[\w:\-/\.]+)/file/(?P<fname>[\w\-\.]+))\b#',
		        array($this, '__fix_links_callback'),
		        $p->body,
		        -1,
		        $count
		    );
		    
		    if ($count && $values['write-changes'])
		    {
		    	$p->body = $changed;
		      	$p->save();
		    }
    	}
    	
    	etag('h3', count($this->fixed) . ($values['write-changes']?' urls were found and fixed!':' urls were found.'));
    	$ul = etag('ul');
    	
		foreach($this->fixed as $c)
			$ul->append(tag('li',
				"{$c['orig-url']} => {$c['new-url']}"
			));		
    }
}