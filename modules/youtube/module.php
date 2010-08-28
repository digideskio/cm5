<?php

class CMS_Module_YouTube extends CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'youtube',
            'title' => 'YouTube embeded video',
            'description' => 'Capture YouTube urls inside articles and make them embeded videos.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
    }
    
    public function default_config()
    {
        return array(
            'video-width' => '425',
            'video-height' => '344',
            'privacy-enchanced' => true,
            'border' => false,
            'controls-color-1' => 'b1b1b1',
            'controls-color-2' => 'd2d2d2',
        );
    }
    
    public function on_save_config()
    {
        CMS_Core::get_instance()->invalidate_page_cache(null);
    }
    
    public function config_options()
    {
        return array(
             'video-width' => array('display' => 'Video width:'),
             'video-height' => array('display' => 'Video height:'),
             'controls-color-1' => array('display' => 'Control color 1:', 'type' => 'color'),
             'controls-color-2' => array('display' => 'Control color 2:', 'type' => 'color'),
             'border' => array('display' => 'Show border:', 'type' => 'checkbox'),
             'privacy-enchanced' => array('display' => 'Privacy enchanced (cookie less youtube):',
                'type' => 'checkbox'),
        );                    
    }
    
    public function create_embed_code($matches)
    {
        $vid = $matches[1];
        $host = ($this->get_config()->{'privacy-enchanced'}?'www.youtube-nocookie.com':'www.youtube.com');
        $color1 = $this->get_config()->{'controls-color-1'};
        $color2 = $this->get_config()->{'controls-color-2'};
        $width =  $this->get_config()->{'video-width'};
        $height =  $this->get_config()->{'video-height'};
        
        $link = "http://{$host}/v/{$vid}?fs=1&amp;hl=en_US&amp;color1=0x{$color1}&amp;color2=0x{$color2}";
        if ($this->get_config()->border)
            $link .= '&amp;border=1';
        
        return "<object width=\"{$width}\" height=\"{$height}\"><param name=\"movie\" value=\"${link}\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"${link}\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"{$width}\" height=\"{$height}\"></embed></object>";
    }
    
    private function replace_links(Page $p)
    {

        if (strstr($p->body, 'www.youtube.com/watch') === false)
            return;

        $p->body = preg_replace_callback('#\bhttp://www.youtube.com/watch\?v=(?P<vid>[\w\-]+)[&\w=\-;]*#m',
            array($this, 'create_embed_code'),
            $p->body);
    }
    
    
    //! Handler for pre rendering
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        
        // Execute subpages
        $this->replace_links($p);
    }
}

CMS_Module_YouTube::register();
?>
