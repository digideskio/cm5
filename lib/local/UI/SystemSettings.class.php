<?php

class UI_SystemSettings extends Output_HTML_Form
{
    public function __construct()
    {
        $config = GConfig::get_instance();
        
        $zone_identifiers = DateTimeZone::listIdentifiers();
        $tzones = array();
        foreach($zone_identifiers as $zone)
            $tzones[$zone] = $zone;
            
        parent::__construct(array(
            'site-title' => array('display' => 'Site title:', 'value' => $config->site->title),
            'site-timezone' => array('display' => 'Timezone:', 'type' => 'dropbox', 'optionlist' => $tzones,
                'value' => $config->site->timezone),
            'email-administrator' => array('display' => "Administrator's mail:", 'value' => $config->email->administrator,
                'hint' => 'The mail that will receive notifications for the site.'),
            'email-sender' => array('display' => "Sender mail address:", 'value' => $config->email->sender,
                'hint' => 'The sender of the site notifications.')
            ),
            array('title' => 'System settings',
                'css' => array('ui-form'),
		        'buttons' => array(
		            'save' => array('display' => 'Save')
                )
            )
        );
    }
    
    public function on_valid($values)
    {
        $config = GConfig::get_writable_copy();
        $config->site->title = $values['site-title'];
        $config->site->timezone = $values['site-timezone'];
        $config->email->administrator = $values['email-administrator'];
        $config->email->sender = $values['email-sender'];
        
        GConfig::update($config);
        CM5_Logger::get_instance()->notice("System settings have been changed.");
        UrlFactory::craft('system.settings')->redirect();
    }
};

?>
