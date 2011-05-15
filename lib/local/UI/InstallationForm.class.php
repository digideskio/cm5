<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */


class UI_InstallationForm extends Output_HTML_Form
{
    public $config_file;

    public $db_build_file;
    
    public $tzones = array();
    
    public function __construct($config_file, $db_build_file = null)
    {
        $this->config_file = $config_file;
        $this->db_build_file = $db_build_file;
        $zone_identifiers = DateTimeZone::listIdentifiers();
        foreach($zone_identifiers as $zone)
            $this->tzones[$zone] = $zone;
        parent::__construct(array(
            'site-title' => array('display' => 'Title', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
            'db' => array('type' => 'custom', 'value' => '<h4>Database Options</h4>'),
			'db-host' => array('display' => 'Host', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
            'db-schema' => array('display' => 'Database', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
			'db-user' => array('display' => 'Username', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
			'db-pass' => array('display' => 'Password', 'type' => 'password', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
			'db-pass2' => array('display' => '', 'type' => 'password', 'regcheck' => '/^.+$/',
				'onerror' => 'This field is mandatory.'),
            'db-prefix' => array('display' => 'Tables prefix', 
				'hint' => 'You can set this to a custom string to avoid naming collision.'),
			'db-build' => array('display' => 'Execute database creation script', 'type' => 'checkbox'),
            'hr-other' => array('type' => 'custom', 'value' => '<h4>Other Options</h4>'),
            'timezone' => array('display' => 'Default timezone', 'type' => 'dropbox',
                'optionlist' => $this->tzones,
                'onerror' => 'Select a valid time zone'),
            ),
        array('title' => '', 'css' => array('ui-form', 'ui-installation'),
		    'buttons' => array(
		        'Save' => array('display' =>'Install'),
                )
            )
        );
    }

    public function on_post()
    {   $values = $this->field_values();
    
        if ($this->get_field_value('db-pass') != $this->get_field_value('db-pass2'))
        {
            $this->invalidate_field('db-pass2', 'The two password are not the same');
        }
        if ($this->is_valid())
        {
            // Try to connect
            if (!DB_Conn::connect($values['db-host'], $values['db-user'], $values['db-pass'], $values['db-schema']))
                $this->invalidate_field('db-host', 'Error connecting on database.');
        }
    }
    
    public function on_valid($values)
    {
        $config = new Zend_Config(array(
            'db' => array(),
            'site' => array(),
            'module' => array(),
            'email' => array()
        ), true);
        $config->db->host = $values['db-host'];
        $config->db->user = $values['db-user'];
        $config->db->pass = $values['db-pass'];
        $config->db->schema = $values['db-schema'];
        $config->db->prefix = $values['db-prefix'];
        $config->site->upload_folder = realpath(__DIR__ . '/../../../uploads');
        $config->site->cache_folder = realpath(__DIR__ . '/../../../cache');
        $config->site->theme = 'default';
        $config->site->title = $values['site-title'];
        
        // Timezone
        if (isset($this->tzones[$values['timezone']]))
            $config->site->timezone = $this->tzones[$values['timezone']];
        
        // Write data
        $conf_writer = new Zend_Config_Writer_Array(array('config' => $config,
            'filename' => $this->config_file));
        $conf_writer->write();
        
        // Reset database
        DB_Conn::connect($config->db->host, $config->db->user, $config->db->pass, $config->db->schema);

        if ($values['db-build'])
        {
            $dbprefix = $config->db->prefix;
            if (DB_Conn::get_link()->multi_query(require($this->db_build_file)))
                while (DB_Conn::get_link()->next_result());
            
            if (DB_Conn::get_link()->errno !== 0)
                etag('strong class="error" nl_escape_on', 'Error executing SQL build script.\n' .
                    DB_Conn::get_link()->error);
        }
        
        
        // Clear cache folder
	    if (($dh = opendir($config->site->cache_folder)) !== FALSE)
	    {
		    while((($entry = readdir($dh)) !== FALSE))
    		{	
    		    if (!is_file($config->site->cache_folder . '/' . $entry))
    				continue;
			    
			    if ($entry[0] == '.')   // Skip hidden files
			        continue;
			        
				unlink($config->site->cache_folder . '/' . $entry);
            }
        }
        			
        // Show result
        $this->hide();
        
        etag('strong', 'Installation finished succesfully !');
        etag('p class="error"', 'For security reasons you must delete folder "install" from web server.');
        
        $relative_folder = implode('/', array_slice(explode('/', dirname($_SERVER['SCRIPT_NAME'])), 0, -1));        
        if (!empty($relative_folder))
            etag('p class="error"', 'Site is running under a subdirectory, for proper support of ' .
                'cool urls, the .htaccess file must be edit and the option ', tag('strong', 'RewriteBase'),
                ' should be change to: ',
            tag('pre class="code"', "RewriteBase $relative_folder")
            );
    }
}
?>
