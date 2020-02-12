create schema crocs collate latin1_swedish_ci;

create table user (
    id int auto_increment
        primary key,
    name varchar(128) not null,
    password varchar(255) not null,
    permissions enum ('deacon', 'elder', 'regional') not null,
    email varchar(128) not null
    );

create table mentor_relationship (
    id int auto_increment
        primary key,
    mentee int null,
    mentor int null,
    accepted tinyint(1) default 0 null,
    constraint mentor_relationship_user_id_fk
        foreign key (mentee) references user (id),
    constraint mentor_relationship_user_id_fk_2
        foreign key (mentor) references user (id)
    );

INSERT INTO crocs.user (id, name, password, permissions, email)
VALUES (1, 'benjamin james', '$2y$12$jN3lA7A6lwbByKkScjqb5OoHwxjA.D.y2lhAsN0.7VndHvcLerwMm', 'regional',
        'benjamin.james98@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email)
VALUES (2, 'kiran walker', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'elder',
        'kirangwalker@gmail.com');

INSERT INTO crocs.mentor_relationship (id, mentee, mentor, accepted)
VALUES (1, 1, 2, 0);