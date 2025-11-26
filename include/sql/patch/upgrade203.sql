begin;

create table parameter_internal (pi_id text not null primary key, pi_value text);
comment on table parameter_internal  is 'Internal parameter, used by the application , it can''t not be changed by the interface';


insert into version (val,v_description) values (204,'Create tables');

commit;
