<?php

    class UI_UploadsGrid extends Output_HTML_Grid
    {
        public function __construct($files)
        {
            $this->files = $files;
            parent::__construct(
                array(
                    'id' => array('caption' => 'ID'),
                    'filename' => array('caption' => 'Filename', 'customdata' => 'true'),
                    'info' => array('caption' => 'Info', 'customdata' => 'true'),
                    'tools' => array('caption' => 'Actions', 'customdata' => 'true')
                ),
                array(
                    'css' => array('ui-grid', 'ui-grid-uploads')
                ), 
                $this->files
            );
        }
        
        public function on_custom_data($col_id, $row_id, $record)
        {
            if ($col_id == 'info')
            {
                $res = tag('ul class="info"');
                if ($record->is_image)
                {
                    $res->append(tag('li', 'Image size: ',
                        tag('span class="imagesize"', "{$record->image_width} x {$record->image_height}")));
                }
                $res->append(tag('li', 'File size: ',
                    tag('span class="size"', html_human_fsize($record->filesize, ''))));
                $res->append(tag('li', 'Author: ',
                    tag('span class="author"', $record->uploader)));
                $res->append(tag('li', 'Last updated: ',
                    tag('span class="updated"', date_exformat($record->lastmodified)->human_diff())));

                return $res;
            }
            else if ($col_id == 'tools')
            {
                return tag('ul class="actions"',
                    tag('li',
                        UrlFactory::craft('upload.edit', $record->id)->anchor('Edit')->add_class('edit')),
                    tag('li',
                        UrlFactory::craft('upload.delete',  $record->id)->anchor('Delete')->add_class('delete'))
                );
                return $res;
            }
            else if ($col_id == 'filename')
            {
                $res = '';
                if ($record->is_image)
                    $res .= tag('img class="thumb"', 
                        array('alt' => $record->filename),
                        array('src' => UrlFactory::craft('upload.thumb', $record))
                    );

                 $res .= tag('span class="url"', (string)UrlFactory::craft_fqn('upload.view', $record));
                 $res .= UrlFactory::craft('upload.view', $record)->anchor('link')->add_class('download');
                 $res .= tag('p', $record->description);
                 return $res;
            }
        }
        
        public function on_mangle_data($col_id, $row_id, $data)
        {

        }
    }

?>
