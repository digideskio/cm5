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

class CM5_Module_ContentMagic extends CM5_Module
{
    //! The name of the module
    public function onRequestMetaInfo()
    {
        return array(
            'nickname' => 'content-magic',
            'title' => 'Content Magic',
            'description' => 'Provides a set of magic keywords to add subpages indexing, redirection etc.'
        );
    }
    
    //! Initialize module
    public function onInitialize()
    {
        $c = CM5_Core::getInstance();
        $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
        $c->events()->connect('page.post-render', array($this, 'event_post_render'));
    }
    
    private function replace_subpages(CM5_Model_Page $p)
    {
        if (strstr($p->body, '##subpages##') === false)
            return;
        
        // Create contents index
        $subpages = CM5_Model_Page::open_query()
            ->where('status = ?')
            ->where('parent_id = ?')
            ->order_by('order', 'ASC')
            ->execute('published', $p->id);
        
        $contents_el = tag('div class="subpages-index"', $ul = tag('ul'));
        foreach($subpages as $sp)
            $ul->append(tag('li', UrlFactory::craft('page.view', $sp)->anchor($sp->title)));
            
        $p->body = str_replace('##subpages##', (string)$contents_el, $p->body);
    }
    
    private function execute_redirect(CM5_Model_Page $p, CM5_Response $r)
    {
        if (strstr($p->body, '##redirect ') === false)
            return;

        if (!preg_match('/##redirect\s+(?P<url>(http)?|.+)\s*##/m', $r->document, $matches))
            return;

        if (empty($matches['url']))
            return;
                
        $r->addHeader('Location: ' . $matches['url']);

    }
    
    //! Handler for pre rendering
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        
        // Execute subpages
        $this->replace_subpages($p);
    }
    
    public function event_post_render($event)
    {
        $resp = $event->filtered_value;
        
        // Execute subpages
        $this->execute_redirect($event->arguments['page'] , $resp);
    }
}

CM5_Module_ContentMagic::register();
