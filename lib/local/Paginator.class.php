<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */


/**
 * A handy paginator class for all purposes.
 */
class Paginator extends ArrayObject
{
	/**
	 * Actual data that will be paginated.
	 * @var array
	 */
	private $data;
	
	/**
	 * The size of each page
	 * @var integer
	 */
	private $items_per_page;	
	
	/**
	 * The current page index.
	 * @var integer
	 */
	private $current_index;
	
	/**
	 * Construct a new paginator on an existing data set.
	 * @param array $data The array with all data.
	 * @param integer $items_per_page The maximum number of items per page.
	 * @param mixed $current_page The index of the current page. 
	 */
	public function __construct($data, $items_per_page, $current_page = null)
	{
		$this->data = $data;
		$this->items_per_page = ($items_per_page > 1)?$items_per_page:1;
		$total_pages = (count($this->data) + $this->items_per_page - 1) / $this->items_per_page; // fast ceil()		
		for($i = 1; $i <= $total_pages; $i++)
			parent::offsetSet($i, new Paginator_Page($this, $i));
		$this->setCurrentIndex($current_page);
	}
	
	/**
	 * Do not permit to change any entry.
	 * @param mixed $index
	 * @param mixed $newval
	 */
	public function offsetSet($index, $newval)
	{
		return false;
	}
	
	/**
	 * Get the data that paginator is binded at
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * Get all pages of this paginator
	 * @return array of Paginator_Page
	 */
	public function getPages()
	{
		return $this;
	}	

	/**
	 * Get the current page index
	 * @return integer
	 */
	public function getCurrentIndex()
	{
		return $this->current_index;
	}
	
	/**
	 * Set the current page index
	 * @param $index A valid indexer
	 */
	public function setCurrentIndex($current_index)
	{
		$this->current_index = ($current_index >= 1) && ($current_index <= $this->count())
			?$current_index
			:1;
	}
	
	/**
	 * Get the actual current page object.
	 * @return Paginator_Page
	 */
	public function getCurrentPage()
	{
		return $this->getPage($this->current_index);
	}
	
	/**
	 * Get a specific page based on its index
	 * @param integer $index
	 */
	public function getPage($index)
	{
		return $this->offsetGet($index);		
	}
	
	/**
	 * Get the first page
	 * @return Paginator_Page
	 */
	public function getFirstPage()
	{
		return $this->offsetGet(1);
	}
	
	/**
	 * Get the last page
	 * @return Paginator_Page
	 */
	public function getLastPage()
	{
		return $this->offsetGet($this->count());
	}
	
	/**
	 * Get the maximum items per page.
	 * @return integer
	 */
	public function getItemsPerPage()
	{
		return $this->items_per_page;
	}
	
	/**
	 * Check if data are actually paginated
	 * @return boolean
	 *  - @b true If there is more than one page.
	 *  - @b false If there is only one page.
	 */
	public function isPaginated()
	{
		return count($this) > 1;
	}
}
