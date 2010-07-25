<?php
return <<< EOF
DROP TABLE IF EXISTS `{$dbprefix}uploads`;
DROP TABLE IF EXISTS `{$dbprefix}pages`;
DROP TABLE IF EXISTS `{$dbprefix}memberships`;
DROP TABLE IF EXISTS `{$dbprefix}users`;
DROP TABLE IF EXISTS `{$dbprefix}groups`;

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
