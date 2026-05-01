-- Phase 3: Database Implementation
-- AdoptME: Web-Based Pet Adoption Management System
-- Submitted by: Chyril S. Manatad | Reynaldo F. Balais

create database adoptme;

use adoptme;

create table users (
user_id int auto_increment primary key,
first_name varchar(50) not null,
middle_name varchar(50),
last_name varchar(50) not null,
email varchar(100) not null unique,
password varchar(255) not null,
phone_number varchar(20),
role enum('admin', 'adopter') not null,
profile_image varchar(255),
created_at timestamp default current_timestamp
);

create table categories (
category_id int auto_increment primary key,
category_name varchar(50) not null
);

create table breeds (
breed_id int auto_increment primary key,
breed_name varchar(50) not null,
category_id int,
foreign key (category_id) references categories(category_id) on delete set null on update cascade
);

create table pets (
pet_id int auto_increment primary key,
name varchar(50) not null,
age int,
gender enum('male', 'female'),
description text,
status enum('available', 'pending', 'adopted') default 'available',
breed_id int,
created_at timestamp default current_timestamp,
foreign key (breed_id) references breeds(breed_id) on delete set null on update cascade
);

create table pet_images (
image_id int auto_increment primary key,
pet_id int,
image_path varchar(255),
foreign key (pet_id) references pets(pet_id) on delete cascade on update cascade
);

create table applications (
application_id int auto_increment primary key,
user_id int,
pet_id int,
message text,
status enum('pending', 'approved', 'rejected') default 'pending',
applied_at timestamp default current_timestamp,
reviewed_at timestamp null,
reviewed_by int,
foreign key (user_id) references users(user_id) on delete cascade on update cascade,
foreign key (pet_id) references pets(pet_id) on delete cascade on update cascade,
foreign key (reviewed_by) references users(user_id) on delete set null on update cascade
);

create table logs (
log_id int auto_increment primary key,
user_id int,
action varchar(100),
description text,
created_at timestamp default current_timestamp,
foreign key (user_id) references users(user_id) on delete set null on update cascade
);
