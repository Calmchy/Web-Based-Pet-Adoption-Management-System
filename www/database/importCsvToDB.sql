-- adoptme: csv import script
-- run with: doas mariadb --local-infile=1 adoptme < /home/calmchy/Documents/csv/import_csv.sql

use adoptme;

-- disable fk checks during import to avoid ordering issues
set foreign_key_checks = 0;

-- address
load data local infile '/home/calmchy/Documents/csv/address.csv'
into table address
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(address_id, @sitio_purok, @subdivision_name, barangay_name, city_town, province, region, zip_code)
set
sitio_purok = nullif(@sitio_purok, ''),
subdivision_name = nullif(@subdivision_name, '');

-- categories
load data local infile '/home/calmchy/Documents/csv/categories.csv'
into table categories
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(category_id, category_name);

-- breeds
load data local infile '/home/calmchy/Documents/csv/breeds.csv'
into table breeds
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(breed_id, breed_name, category_id);

-- users (address must be loaded first due to fk on address_id)
load data local infile '/home/calmchy/Documents/csv/users.csv'
into table users
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(user_id, first_name, middle_name, last_name, email, password, phone_number, role, profile_image, address_id, created_at);

-- pets
load data local infile '/home/calmchy/Documents/csv/pets.csv'
into table pets
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(pet_id, name, age, gender, description, status, breed_id, created_by, created_at);

-- pet_images
load data local infile '/home/calmchy/Documents/csv/pet_images.csv'
into table pet_images
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(image_id, pet_id, image_path);

-- applications (handle nullable reviewed_at and reviewed_by)
load data local infile '/home/calmchy/Documents/csv/applications.csv'
into table applications
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(application_id, user_id, pet_id, message, status, applied_at, @reviewed_at, @reviewed_by)
set
reviewed_at = nullif(@reviewed_at, ''),
reviewed_by = nullif(@reviewed_by, '');

-- notifications
load data local infile '/home/calmchy/Documents/csv/notifications.csv'
into table notifications
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(notification_id, user_id, pet_id, application_id, type, message, is_read, created_at);

-- logs
load data local infile '/home/calmchy/Documents/csv/logs.csv'
into table logs
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 rows
(log_id, user_id, action, description, created_at);

-- re-enable fk checks
set foreign_key_checks = 1;

-- verify row counts
select 'address' as table_name, count(*) as total from address union all
select 'categories', count(*) from categories union all
select 'breeds', count(*) from breeds union all
select 'users', count(*) from users union all
select 'pets', count(*) from pets union all
select 'pet_images', count(*) from pet_images union all
select 'applications', count(*) from applications union all
select 'notifications', count(*) from notifications union all
select 'logs', count(*) from logs;