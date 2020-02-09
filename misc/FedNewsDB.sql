drop schema if exists FedNews;

create schema FedNews collate latin1_swedish_ci;

create table Article (
    id int auto_increment,
    title varchar(128) not null,
    text varchar(5000) not null,
    audience enum ('public', 'student', 'staff') not null,
    author varchar(128) not null,
    reviewed enum ('pending', 'approved', 'rejected') default 'pending' not null,
    date date not null,
    image varchar(128) not null,
    constraint Article_id_uindex
        unique (id)
    );

alter table Article
    add primary key (id);

create table User (
    username varchar(128) not null
        primary key,
    password varchar(255) not null,
    permissions enum ('student', 'staff', 'admin') not null
    );



INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (1, 'Free TAFE programs a boost for female students', 'Female students at Federation University are taking up trade and vocational education programs in record numbers following the introduction of Free TAFE programs.

The near doubling of female enrolments â€“ from 351 in 2018 to 685 this year â€“ is largely credited to the introduction of Free TAFE.

Federation TAFE has about 800 students undertaking 23 Free TAFE programs, around 500 of them female students.

To celebrate the growth in enrolments TAFE leadership and government representatives met with five of the female pre-apprenticeship Painting and Decorating students â€“ one of the first Free TAFE groups to finish their course â€“ to congratulate them on their achievements.

This course is delivered as part of the Building and Construction pre-apprenticeship program, which has seen female participation double since the introduction of Free TAFE (seven in 2018, to 14 in 2019).

Top five Free TAFE courses that have seen the biggest rise in female student numbers

500 per cent increase â€“ Certificate I in Furniture Making (1 student 2018, 6 students 2019)
327 per cent increase â€“ Certificate III in Horticulture (11 students 2018, 47 students 2019)
250 per cent increase â€“ Certificate II in Electrotechnology (2 students 2018, 7 students 2019)
167 per cent increase â€“ Certificate III in Agriculture (6 students 2018, 16 students 2019)
108 per cent increase â€“ Certificate III in Individual Support (36 students 2018, 75 students 2019)
Other Free TAFE programs offered at Federation TAFE include plumbing, building and construction, engineering studies, horticulture, commercial cookery and cyber security.

Top five most popular Free TAFE courses for female students

Diploma of Nursing (248 students, 153 Free TAFE)
Certificate III in Individual Support (75 students, 55 Free TAFE)
Diploma of Community Services (59 students, 45 Free TAFE)
Certificate IV in Accounting and Bookkeeping (54 students, 52 Free TAFE)
Certificate III in Horticulture (47 students, 44 Free TAFE)
Quotes attributable to Vice-Chancellor and President, Professor Helen Bartlett

â€œThe Free TAFE programs are proving to be very popular and I welcome all new students enrolling at Federation TAFE. It is an exciting time to be a female TAFE student. Throughout Victoria, female participation in Free TAFE programs has increased from 48 to 57 per cent.â€  

â€œAs the first technical training institute in Australia, Federation TAFE has a strong reputation in creating job-ready graduates.â€',
        'public', '30331949', 'approved', '2019-09-10',
        'https://studentservices.okstate.edu/sites/default/files/weatpromopic.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (2, 'title 2', 'text 2', 'staff', '30331949', 'approved', '2019-09-22', 'CSS/Images/img1.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (3, 'title 3', 'text 2', 'staff', '30331949', 'approved', '2019-09-21', 'CSS/Images/img3.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (4, 'title 4', 'text 2', 'public', '30331949', 'approved', '2019-09-20', 'CSS/Images/img1.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (5, 'title 5', 'text 2', 'public', '30331949', 'approved', '2019-09-19', 'CSS/Images/img2.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (6, 'title 6', 'text 2', 'public', '30331949', 'approved', '2019-09-18', 'CSS/Images/img3.png');
INSERT INTO FedNews.Article (id, title, text, audience, author, reviewed, date, image)
VALUES (7, 'asd', 'hello', 'staff', '30331949', 'approved', '2019-09-12', 'CSS/Images/img2.png');
INSERT INTO FedNews.User (username, password, permissions)
VALUES ('30331949', '$2y$12$YoqlO07S4kO2m.4Nu0AAZuv3zKDGIJ4c2eF0T0YSdhE4leibuDf12', 'student');
INSERT INTO FedNews.User (username, password, permissions)
VALUES ('30331950', '$2y$12$iCDFzj3hhA/gTP20O2UuKusrTvDM.BE9x.ogDdMVLsu6PTBqhRxhy', 'staff');
INSERT INTO FedNews.User (username, password, permissions)
VALUES ('30331951', '$2y$12$gmzU6j7C8sIau.eVU5TC6OLH//GhDKKrI1.3VfexsPq9pt1SFLHz.', 'admin');