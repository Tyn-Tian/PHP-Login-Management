create database php_login_management;

create database php_login_management_test;

create table users(
	id varchar(255) primary key,
    name varchar(255) not null,
    password varchar(255) not null
) engine = InnoDB;

create table sessions(
	id varchar(255),
    user_id varchar(255) not null,
    constraint fk_sessions_user
    foreign key (user_id) references users(id)
) engine = InnoDB;
