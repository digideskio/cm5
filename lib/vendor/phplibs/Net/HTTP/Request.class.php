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


require_once dirname(__FILE__) . '/Cookie.class.php';

//! Manage the native HTTP request
class Net_HTTP_Request
{
    //! Type of request (GET/POST)
    public $type;

    //! Requested url
    public $url;

    //! The virtual host that requested from.
    public $host;

    //! Scheme of the request
    public $scheme;

    //! Cookies send with request
    public $cookies = array();

    //! Parameters send with request
    public $params = array();

    public function __construct($url, $type, )
    {
        $this->url = $url;
        $this->type = $type;
        $this->
    }

    static public get_type()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    static public get_cookie($name)
    {   if (!isset($_COOKIE[$name]))
    return false;

    $cookie = new Net_HTTP_Cookie($name, $_COOKIE[$name]);
    return $cookie;
    }
}
