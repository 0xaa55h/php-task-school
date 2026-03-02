create table users
(
    id        int auto_increment
        primary key,
    username  text not null,
    email     text not null,
    password  text not null,
    latitude  text null,
    longitude text null
);

create table weather_data
(
    id         int auto_increment
        primary key,
    temp       float                              null,
    created_at datetime default CURRENT_TIMESTAMP null,
    user_id    int                                null,
    constraint weather_data_users_id_fk
        foreign key (user_id) references users (id)
);

