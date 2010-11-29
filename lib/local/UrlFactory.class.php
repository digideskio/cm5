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

//! Factory to create urls
class UrlFactory
{

    //! An array with all resources
    static public $resources = array();
    
    //! Force custom host or assume user request
    static public $force_host = null;
    
    //! Register a new url pattern
    static public function register($name, $params, $pattern)
    {
        if (isset(self::$resources[$name]))
            return false;
        self::$resources[$name] = new UrlFactoryResource($name, $params, $pattern);
    }
    
    //! Open a UrlFactoryResource
    static public function open($name)
    {   
        if (!isset(self::$resources[$name]))
            return false;
        return self::$resources[$name];
    }
    
    //! Craft a prepared url
    static public function craft($name)
    {   
        if (!isset(self::$resources[$name]))
            throw new RuntimeException("Cannot find url resource {$name} in UrlFactory.");
        
        $args = func_get_args();
        $args = array_slice($args, 1);
        return new Uri(url(call_user_func_array(array(self::$resources[$name], 'generate'), $args)));
    }
    
    //! Craft an fql url
    static public function craft_fqn($name)
    {   
        if (!isset(self::$resources[$name]))
            throw new RuntimeException("Cannot find url resource {$name} in UrlFactory.");
        
        $host = (self::$force_host !== null?self::$force_host:$_SERVER['HTTP_HOST']);
        $args = func_get_args();
        $args = array_slice($args, 1);
        $absolute = url(call_user_func_array(array(self::$resources[$name], 'generate'), $args));
        
        return new Uri((empty($_SERVER['HTTPS'])?'http':'https') .'://' . $host . $absolute);
    }

}
