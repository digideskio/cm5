<?php

class Page extends DB_Record
{
    static public $table = 'pages';

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'slug',
        'parent_id' => array('fk' => 'Page'),
        'title',
        'excerpt',
        'body',
        'author' => array('fk' => 'User'),
        'status',
        'created',
        'lastmodified' => array('type' => 'datetime'),
        'order'
    );
    
    public function full_path()
    {
        return '/' . $this->slug;
        if (!($p = $this->parent))
            return '/' . $this->slug;
        return $p->full_path() . '/' . $this->slug;
    }
}
Page::events()->connect('op.pre.save', function($e){

    $r = $e->arguments['record'];
    $r->lastmodified = new DateTime();
});

Page::events()->connect('op.pre.create', function($e){
    $e->filtered_value['created'] = new DateTime();
    $e->filtered_value['lastmodified'] = new DateTime();
});

Page::one_to_Many('Page', 'parent', 'subpages');
?>
