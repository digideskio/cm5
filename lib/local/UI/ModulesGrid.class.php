<?php

    class UI_ModulesGrid extends Output_HTML_Grid
    {
        public function __construct($modules)
        {
            $this->modules = $modules;
            parent::__construct(
                array(
                    'enabled' => array('caption' => 'Enabled', 'customdata' => 'true'),
                    'description' => array('caption' => 'Description', 'customdata' => 'true'),
                ),
                array(
                ), 
                $this->modules
            );
        }
        
        public function on_custom_data($col_id, $row_id, $module)
        {
            if ($col_id == 'enabled')
                return tag('input type="checkbox" checked="true" disabled="disabled"');
           
            if ($col_id == 'description')
            {
                $minfo = $module->info();
                $res = tag('span class="title"', $minfo['title']);
                $res .= tag('p class="description"', $minfo['description']);
                return $res;
            }
        }
    }

?>
