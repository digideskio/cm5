<?php


class Group extends DB_Record
{
    static public $table = 'groups';

    static public $fields = array(
        'groupname' => array('pk' => true)
    );
}

?>
