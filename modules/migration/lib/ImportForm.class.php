<?php


class CM5_Module_Migration_ImportForm extends Form_Html
{
	public $archive_upload;
    public $archive;
    private function add_page($p, &$current_pages, $depth = 0)
    {
        $prefix = str_repeat(" ", $depth * 3) . "|--" . (count($p['children'])?"+ ":"- ");
        $current_pages[$p["id"]] = $prefix . $p["title"];
        foreach($p['children'] as $c)
            $this->add_page($c, $current_pages, $depth + 1);
    }
    
    public function __construct($archive_upload)
    {
        $this->archive_upload = $archive_upload;
        $this->archive = simplexml_load_string(gzdecode($archive_upload->getData()));
        
        parent::__construct(null, array(
        		'attribs' => array('class' => 'form'),
                'title' => 'Import archive',
                'buttons' => array(
                    'Import' => array('type' => 'submit')
                )
            )
        );
    }
    
    public function onInitialized()
    {
        $current_pages = array(0 => '+ Root');        
        foreach(CM5_Core::getInstance()->getTree() as $p)
            $this->add_page($p, $current_pages);
    	
        $this->addMany(
        	field_select('root-page', array('label' => 'Root page to import archive.', 
        			'optionlist' => $current_pages,
                    'attribs' => array('class' => 'monospace'))),
        	field_checkbox('flush-pages', 
        		array('label' => 'Or full restore database by replacing current content (pages and files).'))
		);
    }
    
    private static function getLatestUploadId()
    {
    	$max = DB_Conn::query_fetch_all('SELECT max(id) FROM ' .CM5_Model_Upload::get_table());
    	return $max[0][0];
    } 

    public function onProcessValid()
    {

    	$values = $this->getValues();
        if ($values['flush-pages'])
        {
            CM5_Model_Page::raw_query()->delete()->execute();

            foreach($this->archive->xpath('//pages/page') as $p)
            {
                $parent_id = ($p['parent_id'] == ''?null:(string)$p['parent_id']);

                $attributes = array();
                foreach($p->attributes() as $name => $value)
                    $attributes[$name] = (string)$value;
                $attributes['created'] = new DateTime('@' . $attributes['created']);
                $attributes['lastmodified'] = new DateTime('@' . $attributes['lastmodified']);
                $attributes['parent_id'] = $parent_id;
                $attributes['body'] = (string)$p;

                $newpage = CM5_Model_Page::create($attributes);
            }
            
            if (count($this->archive->xpath('//files')))
            {
            	// Delete all files except the uploaded one
                foreach(CM5_Model_Upload::open_all() as $u) {
                	if ($u->id != $this->archive_upload->id)
                    	$u->delete();
                }
                
                
                foreach($this->archive->xpath('//files/file') as $f)
                {
                	// Avoid colision of archive
                	if ((string)$f['id'] == $this->archive_upload->id) {
                		$this->archive_upload->id = self::getLatestUploadId() + 10;
                		$this->archive_upload->save();
                	}
                    $u = CM5_Model_Upload::create(array(
                    	'id' => (string)$f['id'],
                    	'filename' => (string)$f['filename'],
                    	'description' => (string)$f['description'],
                    	'uploader' => (string)$f['uploader']));
                    $data = base64_decode((string)$f);
                    $u->updateFromData($data);
                }
                
            }
        }
        else
        {
            $new_parent = ($values['root-page'] == 0?null:$values['root-page']);
            $old_to_new_ids = array();
            
            foreach($this->archive->xpath('//pages/page') as $p)
            {
                if ($p['system'] == "1")
                    continue;   // Skip system pages in this mode
                    
                if ($p['parent_id'] == '')
                    $parent_id = $new_parent;
                else
                {
                    if (isset($old_to_new_ids[(string)$p['parent_id']]))
                        $parent_id = $old_to_new_ids[(string)$p['parent_id']];
                    else
                    {
                        continue;
                    }
                }
                
                $attributes = array();
                foreach($p->attributes() as $name => $value)
                    $attributes[$name] = (string)$value;

                $attributes['created'] = new DateTime('@' . $attributes['created']);
                $attributes['lastmodified'] = new DateTime('@' . $attributes['lastmodified']);
                unset($attributes['id']);
                $attributes['parent_id'] = $parent_id;
                $attributes['body'] = (string)$p;
                
                $newpage = CM5_Model_Page::create($attributes);
                
                $old_to_new_ids[(string)$p['id']] = $newpage->id;
            }
        }
    }
}