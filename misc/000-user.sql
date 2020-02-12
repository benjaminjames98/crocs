create table user
(
	name varchar(128) not null
		primary key,
	password varchar(255) not null,
	permissions enum('deacon', 'elder', 'regional') not null,
	email varchar(128) not null
);

