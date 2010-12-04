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

//! 
/**
 * Container for the response produced by the CM5_Core.
 * This container apart from the body contains extra meta
 * info that will help in caching.
 * @author sque
 *
 */
class CM5_Response
{
    /**
     * Headers that are part of this response
     * @var array
     */
    protected $headers = array();

    //! The HTMLDoc of this response object
    /**
     * Te document object
     * @var Output_HTMLDoc
     */    
    public $document = null;

    /**
     * A flag if this response can be cached
     * @var boolean
     */
    public $cachable = true;
    
    /**
     * Add header in response
     * @param string $header The full header in the RFC format, ready to deliver.
     */
    public function add_header($header)
    {
        $this->headers[] = $header;
    }
    
    /**
     * Dump this reponse object in output. It will also send headers if any.
     */
    public function show()
    {
        foreach($this->headers as $h)
            header($h);
            
        echo $this->document;
    }
}
