begin;


-- create constraint
alter table operation_tag add constraint tag_operation_uq unique (jrn_id,tag_id);

-- remove duplicate
delete from operation_tag where opt_id in (select a.opt_id from operation_tag a join operation_tag b on (a.jrn_id=b.jrn_id and a.tag_id=b.tag_id) where a.opt_id  < b.opt_id );


alter table action_gestion_comment add agc_comment_raw text;

update action_gestion_comment set agc_comment_raw =agc_comment;


insert into version (val,v_description) values (182,'Prevent to add several time same tag on an operation, action_comment formatting');
commit;