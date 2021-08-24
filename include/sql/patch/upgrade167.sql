begin;
alter table profile add column with_search_card integer;
comment on column profile.with_search_card is 'Display a icon for searching card : 1 display, 0 not displaid';

update profile set with_search_card =1;
insert into version (val,v_description) values (168,'Button search card');
commit ;
