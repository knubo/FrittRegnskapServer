CREATE TABLE IF NOT EXISTS regn_session(
username varchar(25), 
sessionval varchar(255)
);

create table if not exists regn_user(
  username varchar(25),
  pass varchar(15)
);

CREATE TABLE IF NOT EXISTS regn_grouping_head (
  id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  occured DATE,
  description varchar(80)
); 

CREATE TABLE IF NOT EXISTS regn_possessions (
  id INTEGER(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR (80),
  added_by VARCHAR(50),
  created DATE,
  refid INTEGER(5) UNSIGNED,
  stored_at VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS regn_grouping (
  owner INTEGER(6) UNSIGNED,
  regn_line INTEGER(8) UNSIGNED
);

CREATE TABLE IF NOT EXISTS regn_fordring (
  regn_line INT(8) UNSIGNED,
  debet ENUM('-1', '1')
);


CREATE TABLE IF NOT EXISTS regn_happening (
   name VARCHAR(50) PRIMARY KEY,
   creditPost INTEGER(8),
   debetPost INTEGER(8)
);


CREATE TABLE IF NOT EXISTS regn_year_membership (
   memberid INT(6) UNSIGNED,
   year INT(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);

CREATE TABLE IF NOT EXISTS regn_course_membership (
   memberid INT(6) UNSIGNED,
   semester INTEGER(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);

CREATE TABLE IF NOT EXISTS regn_train_membership (
   memberid INT(6) UNSIGNED,
   semester INTEGER(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);


CREATE TABLE IF NOT EXISTS regn_semester (
   semester INTEGER(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   description VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS regn_standard (
   id VARCHAR(20) NOT NULL PRIMARY KEY,
   value VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS regn_line (
   id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   attachnmb INT(5) UNSIGNED,
   occured DATE,
   postnmb INT(5) UNSIGNED,
   description VARCHAR(40),
   month INT(5) UNSIGNED,
   year INT(5) UNSIGNED
);

CREATE TABLE IF NOT EXISTS regn_post (
   id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   line INT(8) UNSIGNED,
   debet ENUM('-1', '1'),
   post_type INT(5) UNSIGNED,
   project INT(8) UNSIGNED,
   person INT(11) UNSIGNED,
   amount NUMERIC(8,2) UNSIGNED
);

create table if not exists regn_project (
   project INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   description VARCHAR(100)
);

create table if not exists regn_people (
   people INT(11) unsigned
);

CREATE TABLE IF NOT EXISTS regn_post_type (
   post_type INTEGER(5) UNSIGNED NOT NULL PRIMARY KEY,
   coll_post INT(5) UNSIGNED,
   detail_post INT(3),
   description VARCHAR(100),
   in_use tinyint
);

CREATE TABLE IF NOT EXISTS regn_coll_post_type (
   id INTEGER(5) UNSIGNED NOT NULL PRIMARY KEY,
   display_order INT(5) UNSIGNED,
   name VARCHAR(80)
);

CREATE TABLE IF NOT EXISTS regn_detail_post_type (
   id INTEGER(3) UNSIGNED NOT NULL PRIMARY KEY,
   name VARCHAR(80),
   category varchar(80),
   subcategory varchar(80)
);

CREATE TABLE IF NOT EXISTS regn_telling (
   id INTEGER(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   regn_line INT(8) UNSIGNED,
   a1000 INT(3) UNSIGNED,
   a500 INT(3) UNSIGNED,
   a200 INT(3) UNSIGNED,	
   a100 INT(3) UNSIGNED,
   a50 INT(3) UNSIGNED,
   a20 INT(3) UNSIGNED,
   a10 INT(3) UNSIGNED,
   a5 INT(3) UNSIGNED,
   a1 INT(3) UNSIGNED,
   a_5 INT(3) UNSIGNED
);

CREATE TABLE IF NOT EXISTS regn_fond_type (
  fond varchar(3) PRIMARY KEY,
  description VARCHAR(50)
);


CREATE TABLE IF NOT EXISTS regn_fond (
   id INTEGER(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   fond varchar(3),
   description VARCHAR(50),
   occured DATE,
   fond_account NUMERIC(8,2),
   club_account NUMERIC(8,2)
);

CREATE TABLE IF NOT EXISTS regn_budsjett (
   year INT(4) UNSIGNED NOT NULL,
   post_type INTEGER(5) UNSIGNED NOT NULL,
   amount NUMERIC(8,2) UNSIGNED,
   predict NUMERIC(8,2) UNSIGNED
);
