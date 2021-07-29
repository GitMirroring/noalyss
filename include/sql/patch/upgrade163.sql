begin;

drop table if exists profile_mobile;
create table profile_mobile (pmo_id serial primary key , me_code text not null , pmo_order int not null, p_id int not null,pmo_default char default '1');

alter table profile_mobile add constraint profile_mobile_profile_fk  foreign key (p_id) references profile (p_id);
alter table profile_mobile add constraint profile_mobile_menu_ref_fk  foreign key (me_code) references menu_ref (me_code);
alter table profile_mobile add constraint profile_mobile_code_uq unique (p_id,me_code);

comment on table profile_mobile is 'Menu for mobile device';
comment on column profile_mobile.pmo_id is 'primary key';
comment on column profile_mobile.me_code is 'Code of menu_ref to execute';
comment on column profile_mobile.pmo_order is 'item order in menu';
comment on column profile_mobile.p_id is 'Profile id ';
comment on column profile_mobile.pmo_default is 'possible values are 1 , the default HTML header (javascript,CSS,...) is loaded ,  0  nothing is loaded from noalyss ';

insert into profile_mobile (p_id,me_code,pmo_order) select p_id , 'AGENDA',10 from profile;
insert into profile_mobile (p_id,me_code,pmo_order) select p_id , 'LOGOUT',20 from profile;


insert into version (val,v_description) values (164,'Menu for small device : mobile');
commit ;
