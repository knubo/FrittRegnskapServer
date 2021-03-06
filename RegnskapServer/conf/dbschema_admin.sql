
create table if not exists installations (
    id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dbprefix varchar(20) not null unique,
    hostprefix varchar(40) not null unique,
    description varchar(80) not null,
    diskquota INTEGER(9),
	wikilogin varchar(40) not null,
    secret varchar(40),
    beta tinyint,
    sqlIdToRun INTEGER(6) unsigned,
    portal_status int,
    portal_title varchar(255),
    reduced_mode int,
    archive_limit int,
    parentdbprefix varchar(20),
    parenthostprefix varchar(40),
    integration varchar(80)
    );

create table if not exists install_info (
    id INTEGER(6) UNSIGNED PRIMARY KEY,
    username varchar(25),
    password varchar(15),
    clubname varchar(80),
    contact varchar(80),
    email varchar(100),
    address varchar(80),
    postnmb varchar(4),
    city varchar(20),
    phone varchar(13),
    completed tinyint default false
);

create table if not exists change_request (
  id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  installation_id INTEGER(6) UNSIGNED,
  action varchar(40),
  addedTime timestamp,
  executedTime timestamp,
  addedBy varchar(80),
  reason varchar(255)

);


create table if not exists sqllist (
    id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    added timestamp,
    sqltorun TEXT,
    secret varchar(40),
    verified tinyint,
    runinbeta tinyint,
	runbetawhen timestamp,
	runinother tinyint,
	runotherwhen timestamp
);

create table if not exists to_install (
	secret varchar(80) not null unique,
	wikilogin varchar(40) not null unique
);

create table if not exists kid_log (
    id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owning_install int,
    occured timestamp,
	transaction_count int,
	transaction_file varchar(80)
);

create table if not exists norwegiancities (
	zipcode INTEGER(4) unsigned,
	city varchar(80),
	street varchar(80)
);

create INDEX streetIndex on norwegiancities(street);
create INDEX zipIndex on norwegiancities(zipcode);

