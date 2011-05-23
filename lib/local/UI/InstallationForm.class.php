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


class UI_InstallationForm extends Form_Html
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
        parent::__construct(null, array(
        	'title' => 'Setup new CM5 installation.', 'attribs' => array('class' => 'form installation'),
		    'buttons' => array(
		        'Save' => array('label' =>'Install'),
                )
            )
        );
        
        $this->addMany(
	        field_text('site-title', array('label' => 'Title of site', 'required' => true)),
	        field_set('db', array('label' => 'Database Options'))
	        ->addMany(
	        	field_text('host', array('label' => 'Server host', 'required' => true)),
	        	field_text('schema', array('label' => 'Database', 'required' => true)),
	        	field_text('user', array('label' => 'Username', 'required' => true)),
	        	field_password('pass', array('label' => 'Password', 'required' => true)),
	        	field_text('prefix', array('label' => 'Tables prefix',
	        		'hint' => 'You can set this to a custom string to avoid naming collision.')),
	        	field_checkbox('build', array('label' => 'Execute database creation script',
	        		'hint' => 'By checking this, database will be rebuilt.'))
			),
			field_select('timezone', array('label' => 'Default timezone', 'optionlist' => $this->tzones))
		);	
    }

    public function onProcessPost()
    {   
    	$values = $this->getValues();
    
        if ($this->isValid())
        {
            // Try to connect
            if (!@DB_Conn::connect($values['db']['host'], $values['db']['user'], $values['db']['pass'], $values['db']['schema']))
                $this->get('db')->get('host')->invalidate('Error connecting on database.');
        }
    }
    
    public function onProcessValid()
    {
    	$values = $this->getValues();
        $config = new Zend_Config(array(
            'db' => array(),
            'site' => array(),
            'module' => array(),
            'email' => array(
        		'transport' => array(
        			'protocol' => 'sendmail',
        			'host' => '',
        			'port' => '',
        			'ssl' => '',
        			'auth' => '',
        			'username' => '',
        			'password' => ''
        		))
        ), true);
        $config->db = $values['db'];
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

        if ($values['db']['build'])
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
    }
}
