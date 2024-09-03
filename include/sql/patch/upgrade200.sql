begin;

alter table jrn_def add column jrn_def_pj_padding int;
update jrn_def set jrn_def_pj_padding=0;
alter table jrn_def alter jrn_def_pj_padding set default  0;
insert into version (val,v_description) values (201,'receipt with padding ');
commit;