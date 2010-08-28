<?php

class Log extends DB_Record
{
    static public function get_table()
    {   
        return GConfig::get_instance()->db->prefix . 'log';
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
        CM5_Logger::get_instance()->warn("Log was reseted.");
    }
}


?>
