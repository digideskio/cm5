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

/**
 * A grid with all modules
 */
class CM5_Widget_ModulesGrid extends Output_HTML_Grid
{
	/**
	 * @param array $modules array of modules.
	 * @param boolean $themes_mode If true it will show in theme mode.
	 */
	public function __construct($modules, $themes_mode = false)
	{
		$this->modules = $modules;
		$this->themes_mode = $themes_mode;

		parent::__construct(
		array(
			'enabled' => array('caption' => 'Status', 'customdata' => 'true'),
			'description' => array('caption' => 'Description', 'customdata' => 'true'),
		),
		array(
		),
		$this->modules
		);
	}

	public function on_custom_data($col_id, $row_id, $module)
	{
		if ($col_id == 'enabled')
		{
			$inp = '';
			if ($this->themes_mode)
			{
				if ($module->getConfigNickname() === CM5_Config::getInstance()->site->theme)
				$inp .= tag('span', 'Active')->add_class('button disabled light-on');
				else
				$inp .= UrlFactory::craft('theme.switch', $module->getConfigNickname())
				->anchor('Switch')->add_class('button light-off');
			}
			else
			{
				// Module
				if ($module->isEnabled())
				$inp .= UrlFactory::craft('module.disable', $module->getConfigNickname())
				->anchor('Enabled')->add_class('button light-on');
				else
				$inp .= UrlFactory::craft('module.enable', $module->getConfigNickname())
				->anchor('Disabled')->add_class('button light-off');
			}
			return $inp;
		}
		 
		if ($col_id == 'description')
		{
			$minfo = $module->getMetainfo();
			$res = tag('span class="title"', $minfo['title']);
			$res .= tag('p class="description"', $minfo['description']);

			foreach($module->getActions() as $a)
			$res.= UrlFactory::craft('module.action', $minfo['nickname'], $a['name'])
			->anchor($a['display'])->add_class('button');

			if (count($module->getConfigurableFields()))
			{
				$res .= UrlFactory::craft(($this->themes_mode?'theme.config':'module.config'), $minfo['nickname'])
				->anchor('Configure')->add_class('button edit');
			}
			return $res;
		}
	}
}
