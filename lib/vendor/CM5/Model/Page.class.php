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

/**
 * Model class for pages table.
 * 
 * @author sque@0x0lab.org
 *
 * @property integer $id
 * @property string $system
 * @property string $slug
 * @property string $uri
 * @property integer $parent_id
 * @property string $title
 * @property string $body
 * @property string $author
 * @property string $status
 * @property DateTime $created
 * @property DateTime $lastmodified
 * @property integer $order
 * 
 * Relations:
 * @property CM5_Model_Page $parent
 * @property array $subpages
 */
class CM5_Model_Page extends DB_Record
{
    static public function get_table()
    {   
        return CM5_Config::getInstance()->db->prefix . 'pages';
    }

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'system' => array('default' => false),
        'slug',
        'uri',
        'parent_id' => array('fk' => 'CM5_Model_Page'),
        'title',
        'body',
        'author' => array('fk' => 'CM5_Model_User'),
        'status',
        'created' => array('type' => 'datetime'),
        'lastmodified' => array('type' => 'datetime'),
        'order'
    );
    
    public function full_path()
    {
        if (!($p = $this->parent))
            return '/' . $this->slug;
        return $p->full_path() . '/' . $this->slug;
    }
    
    public function delete_all()
    {
        foreach($this->subpages->all() as $p)
            $p->delete_all();
        $this->delete();
    }
    
    public function delete_move_orphans()
    {
        // Move all childs to this node's parent
        static::raw_query()
            ->update()
            ->set('parent_id', $this->parent_id)
            ->where('parent_id = ?')
            ->execute($this->id);

        $this->delete();
    }
}

CM5_Model_Page::events()->connect('op.pre.save', function($e) {
    // Update last modified
    $r = $e->arguments["record"];
    $r->lastmodified = new DateTime();
    
    // Update uri
    $r->uri = $r->full_path();
    
    // Log event
    CM5_Logger::getInstance()->info("Page ({$r->id}) - \"{$r->title}\" was changed.");
});

CM5_Model_Page::events()->connect('op.pre.create', function($e) {
    if (!isset($e->filtered_value["created"]))
        $e->filtered_value["created"] = new DateTime();
    if (!isset($e->filtered_value["lastmodified"]))
        $e->filtered_value["lastmodified"] = new DateTime();
    
    if (!isset($e->filtered_value["author"]))
        $e->filtered_value["author"] = Authn_Realm::get_identity()->id();

});

CM5_Model_Page::events()->connect('op.post.create', function($e) {
    $r = $e->arguments["record"];

    // Log event
    CM5_Logger::getInstance()->info("Page ({$r->id}) - \"{$r->title}\" was created.");

    // Update uri
    $r->uri = $r->full_path();
    $r->save();
});

CM5_Model_Page::events()->connect('op.pre.delete', function($e) {
    if ($e->arguments["record"]->system)
        $e->filtered_value = true;

    $r = $e->arguments["record"];
        
    CM5_Logger::getInstance()->notice("Page ({$r->id}) - \"{$r->title}\" was deleted.");
});

CM5_Model_Page::one_to_many('CM5_Model_Page', 'parent', 'subpages');

