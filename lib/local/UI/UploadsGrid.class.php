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
                    $res->append(tag('li', 'Image size: ',
                        tag('span class="imagesize"', "{$record->image_width} x {$record->image_height}")));
                $res->append(tag('li', 'File size: ',
                    tag('span class="size"', html_human_fsize($record->filesize, ''))));
                $res->append(tag('li', 'Last updated: ',
                    tag('span class="size"', date_exformat($record->lastmodified)->human_diff())));

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
                return ($record->is_image?
                        tag('img class="thumb"', array('src' => UrlFactory::craft('upload.thumb', $record->id))):'') .
                    UrlFactory::craft('upload.view', $record->id)->anchor($record->filename) .
                    tag('p', $record->description);
            }
        }
        
        public function on_mangle_data($col_id, $row_id, $data)
        {

        }
    }

?>
