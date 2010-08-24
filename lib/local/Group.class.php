<?php


class Group extends DB_Record
{
    static public function get_table()
    {   
        return GConfig::get_instance()->db->prefix . 'groups';
    }

    static public $fields = array(
        'groupname' => array('pk' => true)
    );
}

?>
