<?php

    class UI_LogGrid extends Output_HTML_Grid
    {
        public function __construct($entries)
        {
            $this->entries = $entries;
            parent::__construct(
                array(
                    'id' => array(),
                    'priorityName' => array('caption' => 'Priority', 'customdata' => true),
                    'message' => array('caption' => 'Message'),
                    'timestamp' => array('caption' => 'Time', 'customdata' => 'true'),
                    'user' => array('caption' => 'User'),
                    'ip' => array('caption' => 'IP'),
                ),
                array(
                    'css' => array('ui-grid', 'ui-grid-log'),
                    'maxperpage' => '50',
                    'pagecontrolpos' => 'both'
                ), 
                $this->entries
            );
        }
        
        public function on_custom_data($col_id, $row_id, $record)
        {
            if ($col_id == 'timestamp')
            {
                return date_exformat($record->timestamp)->human_diff();
            }
            if ($col_id == 'priorityName')
            {
                return (string)tag('span class="priority"', $record->priorityName)->add_class(strtolower($record->priorityName));
            }
        }
        
        public function on_mangle_data($col_id, $row_id, $data)
        {

        }
    }

?>
