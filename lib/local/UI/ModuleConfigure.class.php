<?php

class UI_ModuleConfigure extends Output_HTML_Form
{
    public function __construct($module)
    {
        $this->module = $module;
        $this->mconfig = $module->get_config();

        $fields = array();
        foreach($module->config_options() as $id => $opt)
        {
            $fields[$id] = array('display' => $opt['display']);
            $f =  & $fields[$id];
            if (isset($opt['type']))
            {
                if ($opt['type'] === 'checkbox')
                    $f['type'] = 'checkbox';
                if ($opt['type'] === 'select')
                {
                    $f['type'] = 'dropbox';
                    $f['optionlist'] = $opt['options'];
                }
                if ($opt['type'] === 'textarea')
                    $f['type'] = 'textarea';
                if ($opt['type'] === 'color')
                    $f['htmlattribs'] = array('class' => 'color');

            }
            $f['value'] = $this->mconfig->{$id};
        }   
        parent::__construct(
            $fields,
        array('title' => 'Configure: ' . $this->module->info_property('title'),
            'css' => array('ui-form', 'ui-form-moduleconfig'),
		    'buttons' => array(
		        'upload' => array('display' =>'Save'),
	            'cancel' => array('display' =>'Cancel', 'type' => 'button',
	                'onclick' => "window.location='" . UrlFactory::craft('module.admin') . "'")
                )
            )
        );
    }
    
    public function on_valid($values)
    {
        foreach($values as $id => $value)
            $this->mconfig->{$id} = $value;

        $this->module->save_config();

        return; // Omit redirect not very usefull        
        if ($this->module->module_type() === 'theme')
            UrlFactory::craft('theme.admin')->redirect();
            
        UrlFactory::craft('module.admin')->redirect();
    }
};

?>
