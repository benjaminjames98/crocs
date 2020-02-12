create table user
(
	name varchar(128) not null
		primary key,
	password varchar(255) not null,
	permissions enum('deacon', 'elder', 'regional') not null,
	email varchar(128) not null
);

INSERT INTO user (name, password, permissions, email) VALUES ('benjamin james', '$2y$12$jN3lA7A6lwbByKkScjqb5OoHwxjA.D.y2lhAsN0.7VndHvcLerwMm', 'regional', 'benjamin.james98@gmail.com');
INSERT INTO user (name, password, permissions, email) VALUES ('kiran walker', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'elder', 'kirangwalker@gmail.com');