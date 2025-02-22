#
# Table structure for table 'tx_easylogin2_domain_model_identifiers'
#
CREATE TABLE tx_easylogin2_domain_model_identifiers (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	connectionname varchar(255) DEFAULT '' NOT NULL,
	connectiontype varchar(255) DEFAULT '' NOT NULL,
	useridentifier varchar(255) DEFAULT '' NOT NULL,
	user int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (

	identifiers int(11) unsigned DEFAULT '0' NOT NULL,

);
