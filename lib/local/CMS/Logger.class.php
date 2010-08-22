<?php

require_once 'Zend/Log/Writer/Abstract.php';

class CMS_Log_Writer extends Zend_Log_Writer_Abstract
{
    /**
     * Create a new instance of Zend_Log_Writer_Db
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Db
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self();
    }

    /**
     * Formatting is not possible on this writer
     */
    public function setFormatter(Zend_Log_Formatter_Interface $formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class($this) . ' does not support formatting');
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        $event['timestamp'] = new DateTime($event['timestamp']);
        Log::create($event);
    }
}


//! CMS Core componment
class CMS_Logger
{
    //! Instance of the logger object
    static $logger = null;
    
    //! Get the instance of this logger
    public static function get_instance()
    {
        if (self::$logger !== null)
            return self::$logger;
            
        $format = '%timestamp% %priorityName% (%priority%): [%ip%][%user%] %message%' . PHP_EOL;
        $formatter = new Zend_Log_Formatter_Simple($format);
        
        $writer = new CMS_Log_Writer();
//        $writer->setFormatter($formatter);
        
        $logger = new Zend_Log($writer);
        $logger->setEventItem('user', (Authn_Realm::get_identity()?Authn_Realm::get_identity()->id():null));
        $logger->setEventItem('ip', $_SERVER['REMOTE_ADDR']);
        
        self::$logger = $logger;
        return $logger;
    }
}

?>
