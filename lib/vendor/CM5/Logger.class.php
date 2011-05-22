<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */

require_once 'Zend/Log/Writer/Abstract.php';

class CM5_Log_Writer extends Zend_Log_Writer_Abstract
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
        CM5_Model_Log::create($event);
    }
}


/**
 * Singleton Wrapper for the actuall logger
 * @author sque
 *
 */
class CM5_Logger
{
    /**
     * Instance of the logger object
     * @var Zend_Log
     */
    static $logger = null;
    
    /**
     * Get the instance of this logger
     * @return Zend_Log The actual loging engine.
     */ 
    public static function getInstance()
    {
        if (self::$logger !== null)
            return self::$logger;

        // Simple writer
        $db_writer = new CM5_Log_Writer();
        $db_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::INFO));
        
        // Mail writer
        CM5_Mailer::getInstance();
        $mail = new Zend_Mail();
        $mail->setFrom(CM5_Config::getInstance()->email->sender)
             ->addTo(CM5_Config::getInstance()->email->administrator);
        $mail_writer = new Zend_Log_Writer_Mail($mail);
        $mail_writer->setSubjectPrependText(CM5_Config::getInstance()->site->title . ' | Needs your attention.');
        
        $mail_format = "User: %user%\nIp: %ip%\nTime: %timestamp%\nType: %priorityName% (%priority%)\n\nMessage: %message%" . PHP_EOL;
        $mail_formatter = new Zend_Log_Formatter_Simple($mail_format);

        $mail_writer->setFormatter($mail_formatter);
        $mail_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN));
 
        // Logger
        $logger = new Zend_Log();
        $logger->addwriter($db_writer);
        $logger->addwriter($mail_writer);
        $logger->setEventItem('user', (Authn_Realm::get_identity()?Authn_Realm::get_identity()->id():null));
        $logger->setEventItem('ip', $_SERVER['REMOTE_ADDR']);
        
        self::$logger = $logger;
        return $logger;
    }
}
