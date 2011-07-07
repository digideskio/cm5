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


class CM5_Module_RevisionModel extends DB_Record
{
	public static $next_summary = null;
	
	/**
	 * Set the next revision summary text
	 */
	public static function setNextSummary($text)
	{
		self::$next_summary = $text;
	}
	
	/**
	 * Get nexts revisions summary text
	 * @param string $changed_fields For auto generation of summary.
	 */
	public static function getNextSummary($changed_fields)
	{
		if (($text = self::$next_summary) == null) {
			// autogenerate
			if (count($changed_fields) > 1) {
				$fields = array_slice($changed_fields, 0, -1);
				$fields = implode(', ', $fields);			
				$last = array_slice($changed_fields, -1);
				$fields = array($fields, $last[0]);
				$fields = implode(' and ', $fields);
			} else {
				$fields =$changed_fields[0];
			}
			$verb = count($fields)>1?'were':'was'; 
			$text = "The {$fields} {$verb} changed.";
		}
		self::$next_summary = NULL;
		return $text;
	}
	
    static public function get_table()
    {   
        return CM5_Config::getInstance()->db->prefix . 'mod_revisions_revs';
    }
	
	public static $fields = array(
		'id' => array('ai' => true, 'pk' => true),
		'page_id' => array('fk' => 'CM5_Model_Page'),
		'new_title',
		'old_title',
		'new_slug',
		'old_slug',
		'new_body',
		'old_body',
		'author' => array('fk' => 'CM5_Model_User'),
		'created_at' => array('type' => 'datetime'),
		'ip',
		'summary',
		'type'
	);
	
	/**
	 * write a copy of this destination to a page object
	 * @param CM5_Model_Page $page
	 */
	public function storeCopyToPage(CM5_Model_Page $page)
	{
		$page->body = $this->getFieldValue('body');
		$page->title = $this->getFieldValue('title');
		$page->slug = $this->getFieldValue('slug');
	}
	
	/**
	 * Get the body data for this revision
	 */
	public function getFieldValue($field)
	{
		$new_field = 'new_' . $field;
		$old_field = 'old_' . $field;
		if ($this->$new_field != null)
			return $this->$new_field;
		$older = self::raw_query()
			->select(array($new_field))
			->where("new_{$field} IS NOT NULL")
			->where('page_id = ?')
			->where('id < ?')
			->order_by('id', 'DESC')
			->limit(1)
			->execute($this->page_id, $this->id);
		if (count($older) == 1)
			return $older[0][$new_field];
		$newer = self::raw_query()
			->select(array($old_field))
			->where("old_$field IS NOT NULL")
			->where('page_id = ?')
			->where('id > ?')
			->order_by('id', 'ASC')
			->limit(1)
			->execute($this->page_id, $this->id);
		if (count($newer) == 1)
			return $newer[0][$old_field];
		
		// Last is the value of the current page
		return $this->page->{$field};
	}
	
	public static function createPreview(CM5_Model_Page $p, $title, $slug, $body)
	{
		return self::create(array(
			'new_slug' => $slug,
			'old_slug' => $slug,
			'new_title' => $title,
			'old_title' => $title,
			'new_body' => $body,
			'old_body' => $body,
			'page_id' => $p->id,
			'created_at' => new DateTime(),
			'author' => Authn_Realm::get_identity()->id(),
			'summary' => 'Preview snapshot.',
			'type' => 'preview',
			'ip' => $_SERVER['REMOTE_ADDR']));
	}
}

CM5_Model_Page::one_to_many('CM5_Module_RevisionModel', 'page', 'revisions');
CM5_Model_User::one_to_many('CM5_Module_RevisionModel', 'user', 'revisions');


CM5_Model_Page::events()->connect('op.post.create', function($e) {
	$p = $e->arguments["record"];
	
	CM5_Module_RevisionModel::create(array(
		'new_slug' => $p->slug,
		'new_title' => $p->title,
		'new_body' => $p->body,
		'page_id' => $p->id,
		'created_at' => new DateTime(),
		'author' => Authn_Realm::get_identity()->id(),
		'summary' => 'Page was created.',
		'type' => 'user',
		'ip' => $_SERVER['REMOTE_ADDR']
	));

});

CM5_Model_Page::events()->connect('op.pre.save', function($e) {
	$p = $e->arguments["record"];
	$old_values = $e->arguments["old_values"];
	$summary_changed_fields = array();
		
	$rev = array();		
	if (isset($old_values["body"]) && ($p->body != $old_values["body"])) {
		$rev["new_body"] = $p->body;
		$summary_changed_fields[]  = 'body';
	}
	if (isset($old_values["title"]) && ($p->title != $old_values["title"])) {
		$rev["new_title"] = $p->title;
		$rev["old_title"] = $old_values["title"];
		$summary_changed_fields[]  = 'title';
	}
	if (isset($old_values["slug"]) && ($p->slug != $old_values["slug"])) {
		$rev["new_slug"] = $p->slug;
		$rev["old_slug"] = $old_values["slug"];
		$summary_changed_fields[]  = 'slug';
	}
	
	if (count($rev) > 0) {
		$rev["page_id"] = $p->id;
		$rev["created_at"] = new DateTime();
		$rev["author"] = Authn_Realm::get_identity()->id();
		$rev['summary'] = CM5_Module_RevisionModel::getNextSummary($summary_changed_fields);
		$rev['ip'] = $_SERVER['REMOTE_ADDR'];
		$rev['type'] = 'user';
		$rev = CM5_Module_RevisionModel::create($rev);
		
		// Delete all previews as they are not needed any more
		CM5_Module_RevisionModel::raw_query()
			->delete()
			->where('page_id = ?')
			->where('type = ?')
			->where('id < ?')
			->execute($p->id, 'preview', $rev->id);
	}
	
});