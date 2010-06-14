DROP TABLE IF EXISTS `image_uploads`;
DROP TABLE IF EXISTS `uploads`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `groups`;

-- Create users
create table `users` (
    `username` varchar(50) not null,
    `password` varchar(40) not null,
    `enabled` int(1) not null,
    primary key(`username`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';


-- Create groups
create table `groups` (
    `username` varchar(50) not null,
    `groupname` varchar(50) not null,
    PRIMARY KEY(`username`, `groupname`)
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Create pages
create table `pages` (
    `id` integer auto_increment not null,
    `slug` varchar(255) not null,
    `parent_id` integer,
    `title` varchar(512) not null,
    `excerpt` TEXT not null,
    `body` MEDIUMTEXT not null,
    `author` varchar(50) not null,
    `created` datetime not null,
    `status` enum('published', 'draft') default 'draft',
    PRIMARY KEY(`id`),
    UNIQUE (`slug`),
    INDEX (`parent_id`)
    
) ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Upload files
CREATE TABLE `uploads` (
    `id` integer auto_increment not null,
    `filename` varchar(255) not null,
    `filesize` integer not null,
    `mime` varchar(255) not null,
    primary key(`id`),
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Upload files
CREATE TABLE `uploads` (
    `id` integer auto_increment not null,
    `filename` varchar(255) not null,
    `filesize` integer not null,
    `mime` varchar(255) not null,
    primary key(`id`),
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

-- Images files
CREATE TABLE `image_uploads` (
    `upload_id` integer not null,
    `width` integer not null,
    `height` integer not null,
    primary key(`upload_id`),
)ENGINE=InnoDB
DEFAULT CHARSET='UTF8';

INSERT INTO `users` (`username`, `password`, `enabled`) values ('root', sha1('root'), 1);

