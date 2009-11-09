<?php


class AppConfig {

const DB_HOST_NAME="localhost";
const DB_USER="root";
const DB_PASSWORD="";
const DB_NAME="bsc_kopi";

#Set to 1 if you want authentication.
const USE_AUTHENTICATION=0;

const TIMEZONE="Europe/Oslo";

#Set to 1 if you want to validate email using checkdnsrr - some systems might not support it.
const VALIDATE_EMAIL_USING_CHECKDNSRR=0;

const MYSQLDUMP="/usr/local/mysql/bin/mysqldump";

#Common db prefix for all database.
const DB_PREFIX = "regn_";

#Values for count. Must match CountCoulumns.
function CountValues() {
	return array(1000,500,200,100,50,20,10,5,1,0.5);
}

#Columns in database for count
function CountColumns() {
  return array('a1000','a500','a200','a100','a50','a20','a10','a5','a1','a_5');
}

#Fordring posts
function FordringPosts() {
	return array(1370,1380,1390,1500,1570);
}
#Posts that are to be transfered to next month.
function EndPosts() {
	return array(1904,1905,1906, 1920,1921);
}
#Post that values are transfered to after end of month.
const EndPostTransferPost=9000;
#Post that values are transferred to after end of year.
const EndPostYearTransferPost=8800;
#Posts available in select when registering a membership.
function RegisterMembershipPosts() {
	return array(1920,1905, 2910, 2990);
}
const YearMembershipCreditPost=3920;
const CourseMembershipCreditPost=3925;
const TrainMembershipCreditPost=3926;
const YouthMembershipCreditPost=3927;

#Membership group
const MembershipGroup=4;
#Fond - club account post
const ClubAccountPost=1920;
#
const BBC_FondDebetPost=7795;
#
const BBC_FondKreditPost=3995;
#
const TSO_FondKreditPost=3397;
#
function DivPosts() {
	return array(3999,7990,8400,8500,1370,1380,1390);
}
}

?>
