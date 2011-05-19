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

class CM5_Module_YouTube extends CM5_Module
{
	//! Initialize module
	public function onInitialize()
	{
		$c = CM5_Core::getInstance();
		$c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
	}

	public function getConfigurableFields()
	{
		return array(
             'video-width' => array('label' => 'Video width:'),
             'video-height' => array('label' => 'Video height:'),
             'controls-color-1' => array('label' => 'Control color 1:', 'type' => 'color'),
             'controls-color-2' => array('label' => 'Control color 2:', 'type' => 'color'),
             'border' => array('label' => 'Show borders', 'type' => 'checkbox'),
             'privacy-enchanced' => array('label' => 'Privacy enchanced (cookie less youtube)',
                'type' => 'checkbox'),
        	'use-iframe' => array('label' => 'Use iframe (HTML5 capable)', 'type' => 'checkbox',
        		'hint' => 'Not all the options are supported in iframe mode.')
		);
	}

	public function getDefaultConfiguration()
	{
		return array(
            'video-width' => '425',
            'video-height' => '344',
            'privacy-enchanced' => true,
            'border' => false,
            'controls-color-1' => '#b1b1b1',
            'controls-color-2' => '#d2d2d2',
		);
	}

	public function onSaveConfig()
	{
		CM5_Core::getInstance()->invalidatePageCache(null);
	}

	public function create_embed_code($matches)
	{
		$vid = $matches[1];
		$host = ($this->getConfig()->{'privacy-enchanced'}?'www.youtube-nocookie.com':'www.youtube.com');
		$color1 = trim($this->getConfig()->{'controls-color-1'}, '#');
		$color2 = trim($this->getConfig()->{'controls-color-2'}, '#');
		$width =  $this->getConfig()->{'video-width'};
		$height =  $this->getConfig()->{'video-height'};

		$link = "http://{$host}/v/{$vid}?fs=1&amp;hl=en_US&amp;color1=0x{$color1}&amp;color2=0x{$color2}";
		if ($this->getConfig()->border)
			$link .= '&amp;border=1';

		if ($this->getConfig()->{'use-iframe'})
			return "<iframe title=\"YouTube video player\" class=\"youtube-player\" type=\"text/html\" " .
        		"width=\"${width}\" height=\"{$height}\" src=\"http://{$host}/embed/{$vid}?rel=0\" " .
        		"frameborder=\"0\"></iframe>";
		else
			return "<object width=\"{$width}\" height=\"{$height}\"><param name=\"movie\" value=\"${link}\">" .
        		"</param><param name=\"allowFullScreen\" value=\"true\"></param><param " .
        		"name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"${link}\" " .
        		"type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" " .
        		"allowfullscreen=\"true\" width=\"{$width}\" height=\"{$height}\"></embed></object>";

    }

    private function replace_links(CM5_Model_Page $p)
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

return array(
	'class' => 'CM5_Module_YouTube',
	'nickname' => 'youtube',
	'title' => 'YouTube embeded video',
	'description' => 'Capture YouTube urls inside articles and make them embeded videos.'

);
