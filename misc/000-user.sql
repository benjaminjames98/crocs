create schema crocs collate latin1_swedish_ci;

create table course
(
    id int auto_increment,
    name varchar(128) null,
    constraint competency_id_uindex
        unique (id)
);

alter table course
    add primary key (id);

create table competency
(
    id int auto_increment
        primary key,
    mentor_relationship int null,
    course int not null,
    can_teach tinyint(1) default 0 null,
    can_understand tinyint(1) default 0 null,
    can_demonstrate tinyint(1) default 0 null,
    project_info varchar(512) default '' not null,
    accepted tinyint(1) default 0 null,
    constraint competency_course_id_fk
        foreign key (course) references course (id)
);

create index competency_mentor_relationship_index
    on competency (mentor_relationship);

create table user
(
    id int auto_increment
        primary key,
    name varchar(128) not null,
    password varchar(255) not null,
    permissions enum('deacon', 'elder', 'regional') not null,
    email varchar(128) not null
);

create table mentor_relationship
(
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

INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (1, 'benjamin james', '$2y$12$jN3lA7A6lwbByKkScjqb5OoHwxjA.D.y2lhAsN0.7VndHvcLerwMm', 'regional', 'benjamin.james9@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (2, 'kiran walker', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'elder', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (3, 'kiran walker 1', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker1@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (4, 'kiran walker 2', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (5, 'kiran walker 3', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (6, 'kiran walker 4', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (7, 'kiran walker 5', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (8, 'kiran walker 6', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (9, 'kiran walker 7', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (10, 'kiran walker 8', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');
INSERT INTO crocs.user (id, name, password, permissions, email) VALUES (11, 'kiran walker 9', '$2y$12$9.NzEJq7dTGADHsM172iNun0Y1Z6vJ8mAlVL5dqqOfA9BllpeuYum', 'deacon', 'kirangwalker@gmail.com');

INSERT INTO crocs.course (id, name) VALUES (1, 'FP 1.1 Becoming a Disciple');
INSERT INTO crocs.course (id, name) VALUES (2, 'FP 1.2 Belonging to a Family of Families');
INSERT INTO crocs.course (id, name) VALUES (3, 'FP 1.3 Participating in the Mission of the Church');
INSERT INTO crocs.course (id, name) VALUES (4, 'FP 1.4 Habits of the Heart');
INSERT INTO crocs.course (id, name) VALUES (5, 'FP 2.1 Enjoying Your Relationship');
INSERT INTO crocs.course (id, name) VALUES (6, 'FP 2.2 Passing on Your Beliefs');
INSERT INTO crocs.course (id, name) VALUES (7, 'FP 2.3 Envisioning Fruitful Lifework');
INSERT INTO crocs.course (id, name) VALUES (8, 'FP 2.4 Building for Future Generations');
INSERT INTO crocs.course (id, name) VALUES (9, 'FP 3.1 Handling the Word With Confidence');
INSERT INTO crocs.course (id, name) VALUES (10, 'FP 3.2 Unfolding the Great Commission');
INSERT INTO crocs.course (id, name) VALUES (11, 'FP 3.3 Laying Solid Foundations in the Gospel');
INSERT INTO crocs.course (id, name) VALUES (12, 'FP 3.4 Catching God''s Vision for the Church');
INSERT INTO crocs.course (id, name) VALUES (13, 'FP 3.5 Living in God''s Household');
INSERT INTO crocs.course (id, name) VALUES (14, 'FP 4.1 Teaching the First Principles');

INSERT INTO crocs.mentor_relationship (id, mentee, mentor, accepted) VALUES (2, 4, 1, 1);
INSERT INTO crocs.mentor_relationship (id, mentee, mentor, accepted) VALUES (3, 4, 2, 1);
INSERT INTO crocs.mentor_relationship (id, mentee, mentor, accepted) VALUES (5, 4, 3, 0);

INSERT INTO crocs.competency (id, mentor_relationship, course, can_teach, can_understand, can_demonstrate, project_info, accepted) VALUES (1, 2, 1, 0, 0, 0, '', 1);
INSERT INTO crocs.competency (id, mentor_relationship, course, can_teach, can_understand, can_demonstrate, project_info, accepted) VALUES (2, 2, 8, 0, 0, 0, '', 1);
INSERT INTO crocs.competency (id, mentor_relationship, course, can_teach, can_understand, can_demonstrate, project_info, accepted) VALUES (3, 2, 9, 0, 0, 0, '', 1);
INSERT INTO crocs.competency (id, mentor_relationship, course, can_teach, can_understand, can_demonstrate, project_info, accepted) VALUES (4, 2, 13, 0, 0, 0, '', 1);