-- phase 3: database implementation
-- adoptme: web-based pet adoption management system
-- submitted by: chyril s. manatad | reynaldo f. balais

create database adoptme;

use adoptme;

create table address (
    address_id int auto_increment primary key,
    sitio_purok varchar(100),
    subdivision_name varchar(100),
    barangay_name varchar(100) not null,
    city_town varchar(100) not null,
    province varchar(100) not null,
    region varchar(100) not null,
    zip_code varchar(10) not null
);

create table users (
    user_id int auto_increment primary key,
    first_name varchar(50) not null,
    middle_name varchar(50),
    last_name varchar(50) not null,
    email varchar(100) not null unique,
    password varchar(255) not null,
    phone_number varchar(20),
    role enum('admin', 'adopter') not null default 'adopter',
    profile_image varchar(255),
    address_id int,
    created_at timestamp default current_timestamp,
    foreign key (address_id) references address(address_id)
        on delete set null
        on update cascade
);

create table categories (
    category_id int auto_increment primary key,
    category_name varchar(50) not null
);

create table breeds (
    breed_id int auto_increment primary key,
    breed_name varchar(50) not null,
    category_id int,
    foreign key (category_id) references categories(category_id)
        on delete set null
        on update cascade
);

create table pets (
    pet_id int auto_increment primary key,
    name varchar(50) not null,
    age int,
    gender enum('male', 'female'),
    description text,
    status enum('available', 'pending', 'adopted') default 'available',
    breed_id int,
    created_by int,
    created_at timestamp default current_timestamp,
    foreign key (breed_id) references breeds(breed_id)
        on delete set null
        on update cascade,
    foreign key (created_by) references users(user_id)
        on delete set null on update cascade
);

create table pet_images (
    image_id int auto_increment primary key,
    pet_id int not null,
    image_path varchar(255),
    foreign key (pet_id) references pets(pet_id)
        on delete cascade
        on update cascade
);

create table applications (
    application_id int auto_increment primary key,
    user_id int not null,
    pet_id int not null,
    message text,
    status enum('pending', 'approved', 'rejected') default 'pending',
    applied_at timestamp default current_timestamp,
    reviewed_at timestamp null,
    reviewed_by int,
    foreign key (user_id) references users(user_id)
        on delete cascade
        on update cascade,
    foreign key (pet_id) references pets(pet_id)
        on delete cascade
        on update cascade,
    foreign key (reviewed_by) references users(user_id)
        on delete set null
        on update cascade
);

create table notifications (
    notification_id int auto_increment primary key,
    user_id int not null,
    pet_id int,
    application_id int,
    type varchar(50) not null,
    message text not null,
    is_read boolean default false,
    created_at timestamp default current_timestamp,
    foreign key (user_id) references users(user_id)
        on delete cascade
        on update cascade,
    foreign key (pet_id) references pets(pet_id)
        on delete set null
        on update cascade,
    foreign key (application_id) references applications(application_id)
        on delete set null
        on update cascade
);

create table logs (
    log_id int auto_increment primary key,
    user_id int,
    action varchar(100),
    description text,
    created_at timestamp default current_timestamp,
    foreign key (user_id) references users(user_id)
        on delete set null
        on update cascade
);