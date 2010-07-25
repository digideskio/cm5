<?php

class CMS_Module_Migration_ExportForm extends Output_HTML_Form
{
    public function __construct()
    {
        $files_size = 0;
        foreach(Upload::raw_query()->select(array('filesize'))->execute() as $f)
            $files_size += $f['filesize'];
        parent::__construct(
            array(
                'files' => array('display' => 'Include also files in exported archive (' . html_human_fsize($files_size) . ')', 'type' => 'checkbox')
            ),
            array(
                'title' => 'Export CMS Content',
                'buttons' => array(
                    'Download' => array('type' => 'submit')
                )
            )
        );
    }
    
    public function on_valid($values)
    {
        Layout::open('admin')->deactivate();

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($archive = $dom->createElement("cms_archive"));
        $archive->setAttribute("version", '1');
        $archive->appendChild($pages = $dom->createElement('pages'));
        foreach(Page::open_all() as $p)
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
        
        if ($values['files'])
        {
            $archive->appendChild($files = $dom->createElement('files'));
            foreach(Upload::open_all() as $f)
            {
                $files->appendChild($file = $dom->createElement('file'));
                $file->setAttribute('id', $f->id);
                $file->setAttribute('filename', $f->filename);
                $file->setAttribute('description', $f->description);
                $file->setAttribute('uploader', $f->uploader);
                $file->appendChild(new DOMText(base64_encode($f->get_data())));
            }
        }

        header('Content-Type: application/x-gzip');
        header("Content-Disposition: attachment; filename=backup.xml.gz");
        echo gzencode($dom->saveXML()); 
        exit;
    }
}

class CMS_Module_Migration_UploadForm extends Output_HTML_Form
{
    public $upload_id = null;
    
    public function __construct()
    {
        $current_uploads = array();
        foreach(Upload::open_all() as $u)
            if (substr($u->filename, -7) == '.xml.gz')
                $current_uploads[$u->id] = "{$u->filename}  (" . date_exformat($u->lastmodified)->human_diff(null, false) . ")" ;
                
        parent::__construct(
            array(
                'uploaded' => array('display' => 'Archives already uploaded on server:', 'type' => 'radio', 'mustselect' => false, 
                    'optionlist' => $current_uploads),
                'new-archive' => array('display' => 'Or upload a new archive on server', 'type' => 'file', 'mustselect' => false,
                    'hint' => '.xml.gz file generated by export process ( limit ' . ini_get('upload_max_filesize') . ')')
            ),
            array(
                'title' => 'Upload archive to server',
                'buttons' => array(
                    'Process' => array('type' => 'submit')
                )
            )
        );
        
        if (empty($current_uploads))
        {
            $f = & $this->get_field('uploaded');
            $f['type'] = 'custom';
            $f['value'] = '&nbsp;&nbsp;&nbsp;(No archive was found...)';
        }
    }
    
    public function on_post()
    {
//        var_dump($this->get_field('uploaded'));
        $newarchive = $this->get_field('new-archive');
        if (($newarchive == null) || (empty($newarchive['value'])))
            return;
        
        if (substr($newarchive['value']['orig_name'], -7) !== '.xml.gz')
            $this->invalidate_field('archive', 'The file must be a valid archive ');
            
        if (gzdecode($newarchive['value']['data']) === false)
            $this->invalidate_field('archive', 'The file must be a valid archive ');
    }
    
    public function on_valid($values)
    {
        var_dump($values);
        if ($values['uploaded'])
        {
            $this->upload_id = $values['uploaded'];
        }
        else if ($values['new-archive'])
        {
            $f = Upload::from_file($values['new-archive']['data'], $values['new-archive']['orig_name']);
            $this->upload_id = $f->id;
        }
    }
}

class CMS_Module_Migration_ImportForm extends Output_HTML_Form
{
    public $archive;
    
    private function add_page($p, &$current_pages, $depth = 0)
    {
        $prefix = str_repeat(" ", $depth * 3) . "|--" . (count($p["childs"])?"+ ":"- ");
        $current_pages[$p["id"]] = $prefix . $p["title"];
        foreach($p["childs"] as $c)
            $this->add_page($c, $current_pages, $depth + 1);
    }
    
    public function __construct($archive, $upload_id)
    {
        $this->upload_id = $upload_id;
        $this->archive = simplexml_load_string(gzdecode($archive->get_data()));
        
        $current_pages = array(0 => '+ Root');        
        foreach(CMS_Core::get_instance()->get_tree() as $p)
            $this->add_page($p, $current_pages);

        parent::__construct(
            array(
                'root-page' => array('display' => 'Root page to import archive.', 'type' => 'dropbox', 'optionlist' => $current_pages,
                    'htmlattribs' => array('class' => 'monospace'), 'mustselect' => true),
                'flush-pages' => array('display' => 'Or full restore database by replacing current content (pages and files).',
                    'type' => 'checkbox',),

            ),
            array(
                'title' => 'Import archive',
                'buttons' => array(
                    'Import' => array('type' => 'submit')
                )
            )
        );
    }

    public function on_valid($values)
    {

        if ($values['flush-pages'])
        {
            Page::raw_query()->delete()->execute();

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

                $newpage = Page::create($attributes);
            }
            
            if (count($this->archive->xpath('//files')))
            {
                foreach(Upload::open_all() as $u)
                    $u->delete();
                
                foreach($this->archive->xpath('//files/file') as $f)
                {
                    $u = Upload::from_file(base64_decode((string)$f), (string)$f['filename']);
                    $u->id = (string)$f['id'];
                    $u->description = (string)$f['description'];
                    $u->uploader = (string)$f['uploader'];
                    $u->save();
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
                        var_dump('skipped', $p);
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
                
                $newpage = Page::create($attributes);
                
                $old_to_new_ids[(string)$p['id']] = $newpage->id;
            }
        }
        
        etag('div class="message"',
            'Import was done completed succesfully. You can now ',
                tag('a', 'delete')->attr('href', (string)UrlFactory::craft('upload.delete', $this->upload_id)),
            ' uploaded archive if you wish.'
        );
        $this->hide();

    }
}

class CMS_Module_Migration extends CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'migration',
            'title' => 'Import/Export pages',
            'description' => 'Add support for importing and exporting pages.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $this->declare_action('import', 'Import', 'request_import');
        $this->declare_action('export', 'Export', 'request_export');
    }
    
    public function request_import()
    {
        if (($fid = Net_HTTP_RequestParam::get('fid')) !== null)
        {
            if (($f = Upload::open($fid)) === false)
                not_found();
            $frm = new CMS_Module_Migration_ImportForm($f, $fid);
            etag('div', $frm->render());
        }
        else
        {
            $frm = new CMS_Module_Migration_UploadForm();
            etag('div', $frm->render());
            if ($frm->upload_id !== null)
            {
                $url = UrlFactory::craft('module.action', $this->info_property('nickname'), 'import') . '?fid=' . $frm->upload_id;
                Net_HTTP_Response::redirect($url);
            }
        }
    }
    
    
    public function request_export()
    {
        $frm = new CMS_Module_Migration_ExportForm();
        etag('div', $frm->render());
    }
}

CMS_Module_Migration::register();
?>
