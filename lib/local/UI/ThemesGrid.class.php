<?php

    class UI_ThemesGrid extends Output_HTML_Grid
    {
        public function __construct($themes)
        {
            $this->themes = $themes;
            parent::__construct(
                array(
                    'selected' => array('caption' => 'Enabled', 'customdata' => 'true'),
                    'description' => array('caption' => 'Description', 'customdata' => 'true'),
                ),
                array(
                ), 
                $this->themes
            );
        }
        
        public function on_custom_data($col_id, $row_id, $theme)
        {
            if ($col_id == 'selected')
            {
                $on = tag('span class="icon" html_escape_off', '&nbsp;');
                $tinfo = $theme->info();
                if (Config::get('site.theme') == $tinfo['nickname'])
                    $on->add_class('light-on');
                else
                    $on->add_class('light-off');
                return $on;
            }
           
            if ($col_id == 'description')
            {
                $minfo = $theme->info();
                $res = tag('span class="title"', $minfo['title']);
                $res .= tag('p class="description"', $minfo['description']);
                return $res;
            }
        }
    }

?>
