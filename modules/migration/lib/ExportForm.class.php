<?php


class CM5_Module_Migration_ExportForm extends Form_Html
{
    public function __construct()
    {
        parent::__construct(null, array(
                'title' => 'Export CMS Content',
            	'attribs' => array('class' => 'form'),
                'buttons' => array(
                    'Download' => array('type' => 'submit')
                )
            )
        );
    }
    
    public function onInitialized()
    {
    	$files_size = 0;
        foreach(CM5_Model_Upload::raw_query()->select(array('filesize'))->execute() as $f)
            $files_size += $f['filesize'];
        $this
	        ->add(field_checkbox('files', array('label' => 'Include also files in exported archive (' . html_human_fsize($files_size) . ')')));
    }
    
    public function onProcessValid()
    {
    	$values = $this->getValues();
        Layout::getActive()->deactivate();

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($archive = $dom->createElement("cms_archive"));
        $archive->setAttribute("version", '1');
        
        // Meta
        $archive->appendChild($meta = $dom->createElement('meta'));
        $meta->appendChild($title = $dom->createElement('title'));
        $title->appendChild(new DomText(CM5_Config::getInstance()->site->title));
        $meta->appendChild($base_url = $dom->createElement('base_url'));
        $base_url->appendChild(new DomText((empty($_SERVER['HTTPS'])?'http':'https') .'://' . $_SERVER['HTTP_HOST'] . surl('/')));
        
        // Pages
        $archive->appendChild($pages = $dom->createElement('pages'));
        foreach(CM5_Model_Page::open_all() as $p)
        {
            $pages->appendChild($page = $dom->createElement('page'));
            $page->setAttribute('title', $p->title);
            $page->setAttribute('id', $p->id);
            $page->setAttribute('parent_id', $p->parent_id);
            $page->setAttribute('order', $p->order);
            $page->setAttribute('slug', $p->slug);
            $page->setAttribute('status', $p->status);
            $page->setAttribute('system', $p->system);
            $page->setAttribute('author', $p->author);
            $page->setAttribute('created', $p->created->format('U'));
            $page->setAttribute('lastmodified', $p->lastmodified->format('U'));
            $page->appendChild(new DOMText($p->body));
        }
        
        // Files
        if ($values['files'])
        {
            $archive->appendChild($files = $dom->createElement('files'));
            foreach(CM5_Model_Upload::open_all() as $f)
            {
                $files->appendChild($file = $dom->createElement('file'));
                $file->setAttribute('id', $f->id);
                $file->setAttribute('filename', $f->filename);
                $file->setAttribute('description', $f->description);
                $file->setAttribute('uploader', $f->uploader);
                $file->appendChild(new DOMText(base64_encode($f->getData())));
            }
        }
    
        $filename = str_replace(' ', '_', CM5_Config::getInstance()->site->title) . '-backup-' . date_create()->format('Y-m-d_H-i');
        header('Content-Type: application/x-gzip');
        header("Content-Disposition: attachment; filename=$filename.xml.gz");
        echo gzencode($dom->saveXML()); 
        exit;
    }
}