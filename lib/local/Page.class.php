<?php

class Page extends DB_Record
{
    static public $table = 'pages';

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'system' => array('default' => false),
        'slug',
        'uri',
        'parent_id' => array('fk' => 'Page'),
        'title',
        'body',
        'author' => array('fk' => 'User'),
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
        Page::raw_query()
            ->update()
            ->set('parent_id', $this->parent_id)
            ->where('parent_id = ?')
            ->execute($this->id);

        $this->delete();
    }
}

Page::events()->connect('op.pre.save', create_function('$e', '
    // Update last modified
    $r = $e->arguments["record"];
    $r->lastmodified = new DateTime();
    
    // Update uri
    $r->uri = $r->full_path();
'));

Page::events()->connect('op.pre.create', create_function('$e', '
    $e->filtered_value["created"] = new DateTime();
    $e->filtered_value["lastmodified"] = new DateTime();
'));

Page::events()->connect('op.post.create', create_function('$e', '
    // Update uri
    $r = $e->arguments["record"];
    $r->uri = $r->full_path();
    $r->save();
'));

Page::events()->connect('op.pre.delete', create_function('$e', '
    if ($e->arguments["record"]->system)
        $e->filtered_value = true;
'));

Page::one_to_Many('Page', 'parent', 'subpages');
?>
