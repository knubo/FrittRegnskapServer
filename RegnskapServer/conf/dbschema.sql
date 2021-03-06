
create table if not exists XXX_log(
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  occured TIMESTAMP,
  username varchar(25),
  category varchar(10),
  action varchar(10),
  message TEXT
);


create table if not exists XXX_accounttrack(
   post INTEGER(8) PRIMARY KEY
);

create table if not exists XXX_happeningv2(
  id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  description varchar(40),
  linedesc varchar(80),
  debetpost INT(5) UNSIGNED,
  kredpost INT(5) UNSIGNED,
  count_req tinyint
);

create table if not exists XXX_user(
  username varchar(25) PRIMARY KEY,
  pass varchar(15),
  person INT(11) unsigned,
  readonly tinyint,
  reducedwrite tinyint,
  project_required tinyint,
  lastlogin TIMESTAMP,
  see_secret tinyint,
  profile TEXT
);

CREATE TABLE IF NOT exists XXX_grouping_head (
  id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  occured DATE,
  description varchar(80)
);

CREATE TABLE IF NOT exists XXX_possessions (
  id INTEGER(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR (80),
  added_by VARCHAR(50),
  created DATE,
  refid INTEGER(5) UNSIGNED,
  stored_at VARCHAR(50)
);

CREATE TABLE IF NOT exists XXX_grouping (
  owner INTEGER(6) UNSIGNED,
  regn_line INTEGER(8) UNSIGNED
);

CREATE TABLE IF NOT exists XXX_fordring (
  regn_line INT(8) UNSIGNED,
  debet ENUM('-1', '1')
);


CREATE TABLE IF NOT exists XXX_year_membership (
   memberid INT(6) UNSIGNED,
   year INT(4) UNSIGNED,
   regn_line INT(8) UNSIGNED,
   youth tinyint
);

CREATE TABLE IF NOT exists XXX_year_price(
   year INTEGER(4) UNSIGNED PRIMARY KEY,
   amount NUMERIC(8,2) UNSIGNED,
   amountyouth NUMERIC(8,2) UNSIGNED
);


CREATE TABLE IF NOT exists XXX_course_membership (
   memberid INT(6) UNSIGNED,
   semester INTEGER(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);

CREATE TABLE IF NOT exists XXX_course_price(
   semester INTEGER(4) UNSIGNED PRIMARY KEY,
   amount NUMERIC(8,2) UNSIGNED
);


CREATE TABLE IF NOT exists XXX_youth_price(
   semester INTEGER(4) UNSIGNED PRIMARY KEY,
   amount NUMERIC(8,2) UNSIGNED
);

CREATE TABLE IF NOT exists XXX_youth_membership (
   memberid INT(6) UNSIGNED,
   semester INTEGER(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);



CREATE TABLE IF NOT exists XXX_train_membership (
   memberid INT(6) UNSIGNED,
   semester INTEGER(4) UNSIGNED,
   regn_line INT(8) UNSIGNED
);



CREATE TABLE IF NOT exists XXX_train_price(
   semester INTEGER(4) UNSIGNED PRIMARY KEY,
   amount NUMERIC(8,2) UNSIGNED
);


CREATE TABLE IF NOT exists XXX_semester (
   semester INTEGER(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   description VARCHAR(20),
   year int,
   fall tinyint
);

CREATE TABLE IF NOT exists XXX_standard (
   id VARCHAR(20) NOT NULL PRIMARY KEY,
   value VARCHAR(100)
);

CREATE TABLE IF NOT exists XXX_line (
   id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   attachnmb INT(5) UNSIGNED,
   occured DATE,
   postnmb INT(5) UNSIGNED,
   description VARCHAR(40),
   month INT(5) UNSIGNED,
   year INT(5) UNSIGNED,
   edited_by_person INT(11) unsigned
);

CREATE TABLE IF NOT exists XXX_post (
   id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   line INT(8) UNSIGNED,
   debet ENUM('-1', '1'),
   post_type INT(5) UNSIGNED,
   project INT(8) UNSIGNED,
   person INT(11) UNSIGNED,
   amount NUMERIC(8,2) UNSIGNED,
   edited_by_person INT(11) unsigned,
   belonging_id INTEGER(6) UNSIGNED
);

create table if not exists XXX_project (
   project INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   description VARCHAR(100)
);

create table if not exists XXX_person (
   id INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
   firstname varchar(50),
   lastname varchar(50),
   email varchar(100),
   address varchar(80),
   postnmb varchar(4),
   city varchar(20),
   country varchar(2),
   phone varchar(13),
   cellphone varchar(13),
   employee tinyint,
   birthdate DATE,
   newsletter tinyint,
   hidden tinyint,
   gender varchar(1),
   secret varchar(40),
   comment TEXT,
   secretaddress tinyint,
   lastedit TIMESTAMP,
   year_membership_required tinyint,
   semester_membership_required tinyint
);

create table if not exists XXX_person_backup (
   id INT(11) unsigned NOT NULL PRIMARY KEY,
   firstname varchar(50),
   lastname varchar(50),
   email varchar(100),
   address varchar(80),
   postnmb varchar(4),
   city varchar(20),
   country varchar(2),
   phone varchar(13),
   cellphone varchar(13),
   employee tinyint,
   birthdate DATE,
   newsletter tinyint,
   gender varchar(1),
   lastedit TIMESTAMP
);

CREATE TABLE IF NOT exists XXX_email_content (
   id INT(5) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name varchar(40) unique NOT NULL,
   content TEXT,
   header tinyint
);

CREATE TABLE IF NOT exists XXX_post_type (
   post_type INTEGER(5) UNSIGNED NOT NULL PRIMARY KEY,
   coll_post INT(5) UNSIGNED,
   detail_post INT(3),
   description VARCHAR(100),
   in_use tinyint
);

CREATE TABLE IF NOT exists XXX_coll_post_type (
   id INTEGER(5) UNSIGNED NOT NULL PRIMARY KEY,
   display_order INT(5) UNSIGNED,
   name VARCHAR(80)
);

CREATE TABLE IF NOT exists XXX_detail_post_type (
   id INTEGER(3) UNSIGNED NOT NULL PRIMARY KEY,
   name VARCHAR(80),
   category varchar(80),
   subcategory varchar(80)
);

CREATE TABLE IF NOT exists XXX_telling (
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

CREATE TABLE IF NOT exists XXX_fond_type (
  fond varchar(3) PRIMARY KEY,
  description VARCHAR(50)
);


CREATE TABLE IF NOT exists XXX_fond (
   id INTEGER(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   fond varchar(3),
   description VARCHAR(50),
   occured DATE,
   fond_account NUMERIC(8,2),
   club_account NUMERIC(8,2),
   accountline INTEGER(8) UNSIGNED
);

create table if not exists XXX_budget_membership (
  year int(4) unsigned not null primary key,
  year_members int(5) unsigned,
  spring_train int(5) unsigned,
  spring_course int(5) unsigned,
  fall_train int(5) unsigned,
  fall_course int(5) unsigned,
  fall_youth int(5) unsigned,
  spring_youth int(5) unsigned,
  year_youth int(5) unsigned
);


CREATE TABLE IF NOT exists XXX_budsjett (
   year INT(4) UNSIGNED NOT NULL,
   post_type INTEGER(5) UNSIGNED NOT NULL,
   amount NUMERIC(8,2) UNSIGNED,
   earning tinyint
);

CREATE TABLE IF NOT exists XXX_fond_action (
   id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   description varchar(40),
   fond varchar(3),
   defaultdesc varchar(50),
   actionclub INTEGER(1),
   actionfond INTEGER(1),
   debetpost INTEGER(5),
   creditpost INTEGER(5)
);

CREATE TABLE IF NOT exists XXX_portal_user (
    pass varchar(15),
    person INT(11) unsigned primary key,
	show_gender bit,
	show_birthdate bit,
	show_cellphone bit,
	show_phone bit,
	show_country bit,
	show_city bit,
	show_postnmb bit,
	show_address bit,
	show_email bit,
	show_lastname bit,
	show_firstname bit,
	show_image bit,
	homepage varchar(255),
	twitter varchar(255),
	linkedin varchar(255),
	facebook varchar(255),
	deactivated bit,
	lastlogin TIMESTAMP

);

create table if not exists XXX_email_archive (
   id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   username varchar(25) not null,
   sent bit,
   subject text,
   body text,
   format varchar(5),
   footer int,
   header int,
   edit_time TIMESTAMP
);

create table if not exists XXX_belonging (
	id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	belonging varchar(80),
	description TEXT,
	serial varchar(80),
	year_deprecation int,
    purchase_price NUMERIC(8,2) UNSIGNED,
    warrenty_date DATE,
    owning_account INT(5) UNSIGNED,
    deprecation_account INT(5) UNSIGNED,
    added_by_person INT(11) unsigned,
    added_date DATE,
    changed_by_person INT(11) unsigned,
    changed_date DATE,
    purchase_date DATE,
    current_price NUMERIC(8,2) UNSIGNED,
    deprecation_amount NUMERIC(8,2) UNSIGNED,
    deleted tinyint,
	person INT(11) unsigned
);

create table if not exists XXX_kid (
    id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shipment_number INTEGER(7) UNSIGNED,
    transaction_number INTEGER(7) UNSIGNED,
    kid varchar(10),
    form_number INTEGER(10) UNSIGNED,
    amount NUMERIC(8,2) UNSIGNED,
    settlement_date DATE,
    regn_line INTEGER(8) UNSIGNED,
    kid_status INTEGER(1) UNSIGNED
);

create table if not exists XXX_event_schema (
    id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form TEXT,
    eventdesc varchar(255),
    active tinyint,
    start_date DATE,
    end_date DATE,
    event_date DATE,
    max_people int
);

create table if not exists XXX_event_partisipant_meta (
    event_id INTEGER(8) unsigned,
    person_id INTEGER(11) unsigned,
    created_time TIMESTAMP,
    changed_time TIMESTAMP,
    PRIMARY KEY (event_id, person_id)
);

create table if not exists XXX_event_partisipant (
    event_id INTEGER(8) unsigned,
    person_id INTEGER(11) unsigned,
    group_key varchar(50),
    group_value TEXT,
    gender ENUM('male','female')
);

create table if not exists XXX_invoice_type (
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  description varchar(255),
  invoice_type int,
  split_type int,
  invoice_due_day int,
  default_amount NUMERIC(8,2) UNSIGNED,
  email_subject text,
  email_body text,
  email_format varchar(5),
  email_footer int,
  email_header int,
  email_from varchar(255),
  invoice_template varchar(255),
  invoice_rows_json TEXT
);


create table if not exists XXX_invoice_top (
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_type INTEGER(8) UNSIGNED,
  created_date DATE,
  created_by_person INT(11) unsigned
);

create table if not exists XXX_invoice (
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_top INTEGER(8) UNSIGNED,
  due_date DATE
);

create table if not exists XXX_invoice_recipient (
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INTEGER(8) UNSIGNED,
  invoice_status int,
  sent_date DATE,
  paid_date DATE,
  deleted_date DATE,
  changed_by_person_id INT(11) unsigned,
  person_id INT(11) unsigned
);

create table if not exists XXX_invoice_payment (
  id INTEGER(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipient_id INTEGER(8),
  invoice_type INTEGER(8) UNSIGNED,
  credit_post_type INTEGER(5) UNSIGNED NULL,
  amount NUMERIC(8,2) UNSIGNED
);
