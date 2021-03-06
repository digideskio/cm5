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

return <<< EOF

-- Create revisions table
CREATE TABLE IF NOT EXISTS `{$dbprefix}mod_revisions_revs` (
	`id` integer auto_increment, 
	`page_id` integer not null,
	`new_title` varchar(512),
	`old_title` varchar(512),
	`new_slug` varchar(256),
	`old_slug` varchar(256),
	`old_body` MEDIUMTEXT,
	`new_body` MEDIUMTEXT,
	`type` ENUM('auto', 'user', 'preview') NOT NULL DEFAULT 'auto',
    `author` varchar(50) NOT NULL,
	`created_at` DATETIME,
	`ip` VARCHAR(45) NOT NULL,
	`summary` VARCHAR(256) NOT NULL,
	PRIMARY KEY(`id`)
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

EOF;
