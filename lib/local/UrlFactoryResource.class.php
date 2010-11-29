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

//! Internal representation of UrlFactory resources
class UrlFactoryResource
{

    //! The name of the resoucre
    private $name;
    
    //! The accepted parameters of this resource
    private $params;
    
    //! The pattern to create this resource
    private $pattern;
    
    //! The cached generator
    private $generator = null;
    
    //! Construct a new resource
    /**
     * @param $name The name of this resource
     * @param $params The accepted parameters of this resource, same syntax as create_function()
     * @param $pattern The pattern to be used to create url.
     */
    public function __construct($name, $params, $pattern)
    {
        $this->name = $name;
        $this->params = $params;
        $this->pattern = $pattern;
    }
    
    //! Generate or return the cached generator
    private function get_generator()
    {
        if ($this->generator != null)
            return $this->generator;
        $this->generator = create_function(
            $this->params,
            "return \"{$this->pattern}\";");
        return $this->generator;
    }
    
    //! Get the accepted parameters
    public function get_params()
    {
        return $this->params;
    }
    
    //! Get the name of the resource
    public function get_name()
    {
        return $this->name;
    }
    
    //! Generate a url with user defined parameters
    public function generate()
    {
        $args = func_get_args();
        $res = call_user_func_array($this->get_generator(), $args);
        return $res;
    }

}

?>
