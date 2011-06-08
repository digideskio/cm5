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
 * Page of paginated data.
 */
class Paginator_Page implements IteratorAggregate, Countable
{
	/**
	 * The paginator that this page belongs to.
	 * @var Paginator
	 */
	protected $owner;
	
	/**
	 * The index of this page on the owner paginator.
	 * @var integer
	 */
	protected $index;
	
	/**
	 * Pages are contructed by paginator
	 * @param Paginator owner The owner paginator.
	 * @param integer $index The index of this page on the owner paginator.
	 */
	public function __construct(Paginator $owner, $index)
	{
		$this->owner = $owner;
		$this->index = $index;		
	}
	
	/**
	 * Get the index of this page.
	 * @return integer
	 */
	public function getIndex()
	{
		return $this->index;
	}
	
	/**
	 * Get all the items of this page
	 * @return array
	 */
	public function getItems()
	{
		return array_slice(
			$this->owner->getData(),
			($this->index -1) * $this->owner->getItemsPerPage(),
			$this->owner->getItemsPerPage(), true);
	}
	
	/**
	 * Alias to getItems()
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getItems());
	}
	
	/**
	 * Get the owner paginator of this page
	 * @return Paginator
	 */
	public function getOwner()
	{
		return $this->owner;
	}
	
	/**
	 * Count items of this page
	 * @return integer
	 */
	public function count()
	{
		return count($this->getItems());
	}
	
	/**
	 * Check if this page is paginators current one.
	 * @return integer
	 */
	public function isCurrent()
	{
		return $this->index == $this->owner->getCurrentIndex();
	}
	
	/**
	 * Check if this is the first page
	 * @return boolean
	 */
	public function isFirstPage()
	{
		return $this->index <= 1;
	}
	
	/**
	 * Check if this is the last page
	 * @return boolean
	 */
	public function isLastPage()
	{
		return $this->index >= $this->owner->getTotalPages();
	}
	
	/**
	 * Get the previous page of this one.
	 * @return Paginator_Page
	 */
	public function getPrevious()
	{
		return $this->isFirstPage()
			?null
			:$this->owner->getPage($this->index - 1);
	}
	
	/**
	 * Get the next page of this one.
	 * @return Paginator_Page
	 */
	public function getNext()
	{
		return $this->isLastPage()
			?null
			:$this->owner->getPage($this->index + 1);
	}
	
	/**
	 * Stringify the index of this page.
	 */
	public function __toString()
	{
		return (string)$this->index;
	}
}
