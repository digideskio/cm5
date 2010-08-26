<?php

    class UI_ModulesGrid extends Output_HTML_Grid
    {
        public function __construct($modules, $themes_mode = false)
        {
            $this->modules = $modules;
            $this->themes_mode = $themes_mode;
            
            parent::__construct(
                array(
                    'enabled' => array('caption' => 'Status', 'customdata' => 'true'),
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
            {
                $inp = '';
                if ($this->themes_mode)
                {
                    if ($module->config_nickname() === GConfig::get_instance()->site->theme)
                        $inp .= tag('span', 'Active')->add_class('button disabled light-on');
                    else
                        $inp .= UrlFactory::craft('theme.switch', $module->config_nickname())
                            ->anchor('Switch')->add_class('button light-off');
                }
                else
                {
                    // Module
                    if ($module->is_enabled())
                        $inp .= UrlFactory::craft('module.disable', $module->config_nickname())
                            ->anchor('Enabled')->add_class('button light-on');
                    else
                        $inp .= UrlFactory::craft('module.enable', $module->config_nickname())
                            ->anchor('Disabled')->add_class('button light-off');
                }
                return $inp;
            }
           
            if ($col_id == 'description')
            {
                $minfo = $module->info();
                $res = tag('span class="title"', $minfo['title']);
                $res .= tag('p class="description"', $minfo['description']);
                
                foreach($module->get_actions() as $a)
                    $res.= UrlFactory::craft('module.action', $minfo['nickname'], $a['name'])
                        ->anchor($a['display'])->add_class('button');

                if (count($module->config_options()))
                {
                    $res .= UrlFactory::craft('module.config', $minfo['nickname'])
                        ->anchor('Configure')->add_class('button edit');
                }
                return $res;
            }
        }
    }
?>
