DROP TABLE IF EXISTS `resource`;
CREATE TABLE `resource` (
    `id` bigint(20) unsigned NOT NULL auto_increment,
    `load_1min` decimal(10,2) default 0,
    `load_5min` decimal(10,2) default 0,
    `load_15min` decimal(10,2) default 0,
    `processes_total` int default NULL,
    `processes_running` int default NULL,
    `memory_free` bigint default NULL,
    `memory_cached` bigint default NULL,
    `memory_buffers` bigint default NULL,
    `memory_kernel` bigint default NULL,
    `uptime` int default NULL,
    `uptime_idle` int default NULL,
    `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
