
create table if not exists installations (
    id INTEGER(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dbprefix varchar(20) not null unique,
    hostprefix varchar(40) not null unique,
    description varchar(80) not null
);
