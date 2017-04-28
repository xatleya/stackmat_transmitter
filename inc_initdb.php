<?php

$charset = " DEFAULT CHARSET=utf8";

$query = <<<EOT
CREATE TABLE IF NOT EXISTS $eventstable (
  `id` tinyint(2) NOT NULL,
  `timelimit` varchar(9) default NULL,
  `r1` tinyint(1) default '0',
  `r1_format` tinyint(1) ,
  `r1_open` tinyint(1) default '0',
  `r2` tinyint(1) default '0',
  `r2_format` tinyint(1) ,
  `r2_open` tinyint(1) default '0',
  `r3` tinyint(1) default '0',
  `r3_format` tinyint(1) ,
  `r3_open` tinyint(1) default '0',
  `r4` tinyint(1) default '0',
  `r4_format` tinyint(1) ,
  `r4_open` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
)
EOT;
$connect->query($query.$charset);

$query = <<<EOT
CREATE TABLE IF NOT EXISTS $compstable (
  `id` SMALLINT( 3 ) NOT NULL ,
  `WCAid` VARCHAR( 10 ) ,
  `name` VARCHAR( 80 ) ,
  `country` VARCHAR( 20 ) ,
  `birthday` DATE ,
  `gender` VARCHAR( 1 ) ,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `WCAid` , `name` )
)
EOT;
$connect->query($query.$charset);

$query = <<<EOT
CREATE TABLE IF NOT EXISTS $regstable (
  `cat_id` tinyint(2) NOT NULL,
  `round` tinyint(1) NOT NULL,
  `comp_id` smallint(3) NOT NULL,
  PRIMARY KEY  (`cat_id`,`round`,`comp_id`)
)
EOT;
$connect->query($query.$charset);

$query = <<<EOT
CREATE TABLE IF NOT EXISTS $timestable (
  `cat_id` tinyint(2) NOT NULL,
  `round` tinyint(1) NOT NULL,
  `comp_id` smallint(3) NOT NULL,
  `t1` varchar(15) NOT NULL,
  `t2` varchar(15) default NULL,
  `t3` varchar(15) default NULL,
  `t4` varchar(15) default NULL,
  `t5` varchar(15) default NULL,
  `average` varchar(15) default NULL,
  `best` varchar(15) default NULL,
  PRIMARY KEY  (`cat_id`,`round`,`comp_id`)
)
EOT;
$connect->query($query.$charset);

?>