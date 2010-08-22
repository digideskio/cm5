<?php

class Log extends DB_Record
{
    static public function get_table()
    {   
        return Config::get('db.prefix') . 'log';
    }

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'timestamp' => array('type' => 'datetime'),
        'message',
        'priority',
        'priorityName',
        'user',
        'ip'
    );
    
    static public function reset()
    {
        Log::raw_query()->delete()->execute();
        CMS_Logger::get_instance()->warn("Log was reseted.");
    }
}


?>
