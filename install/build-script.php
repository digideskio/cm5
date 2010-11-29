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
DROP TABLE IF EXISTS `{$dbprefix}uploads`;
DROP TABLE IF EXISTS `{$dbprefix}pages`;
DROP TABLE IF EXISTS `{$dbprefix}memberships`;
DROP TABLE IF EXISTS `{$dbprefix}users`;
DROP TABLE IF EXISTS `{$dbprefix}groups`;
DROP TABLE IF EXISTS `{$dbprefix}log`;

-- Create users
create table `{$dbprefix}users` (
    `username` varchar(50) not null,
    `password` varchar(40) not null,
    `enabled` int(1) not null,
    primary key(`username`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';


-- Create memberships
create table `{$dbprefix}memberships` (
    `username` varchar(50) not null,
    `groupname` varchar(50) not null,
    PRIMARY KEY(`username`, `groupname`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Create groups
create table `{$dbprefix}groups` (
    `groupname` varchar(50) not null,
    PRIMARY KEY(`groupname`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Create pages
create table `{$dbprefix}pages` (
    `id` integer auto_increment not null,
    `slug` varchar(255) not null,
    `uri` varchar(512) not null,
    `parent_id` integer,
    `title` varchar(512) not null,
    `body` MEDIUMTEXT not null,
    `author` varchar(50) not null,
    `created` datetime not null,
    `lastmodified` datetime not null,
    `status` enum('published', 'draft') default 'draft',
    `system` bool not null default false,
    `order` int default 0,
    PRIMARY KEY(`id`),
    INDEX (`parent_id`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Upload files
CREATE TABLE `{$dbprefix}uploads` (
    `id` integer auto_increment not null,
    `filename` varchar(255) not null,
    `filesize` integer not null,
    `uploader` varchar(50),
    `lastmodified` datetime not null,
    `mime` varchar(255) not null,
    `store_file` varchar(512) not null,
    `description` TEXT not null,
    `is_image` BOOL not null,
    `image_height` integer,
    `image_width` integer,
    `sha1_sum` CHAR(40),
    PRIMARY KEY(`id`),
    UNIQUE KEY(`filename`)
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Log
CREATE TABLE `{$dbprefix}log` (
    `id` integer auto_increment not null,
    `message` varchar(2048) not null,
    `timestamp` DATETIME not null,
    `priority` integer not null,
    `priorityName` varchar(20) not null,
    `user` varchar(50) not null,
    `ip` varchar(45) not null,
    PRIMARY KEY(`id`)
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Default user
INSERT INTO `{$dbprefix}users` (`username`, `password`, `enabled`) values ('root', sha1('root'), 1);

-- Default groups
INSERT INTO `{$dbprefix}groups` (`groupname`) values
    ('admin'),
    ('editor');
    
INSERT INTO `{$dbprefix}memberships` (`username`, `groupname`) values
    ('root', 'admin'),
    ('root', 'editor');
    
-- System pages
INSERT INTO `{$dbprefix}pages` (`system`, `title`, `slug`, `uri`, `body`, `author`, `created`, `lastmodified`, `status`)
    VALUES(true, 'Home', '', '/', '', 'root', NOW(), NOW(), 'published');
EOF;
?>
