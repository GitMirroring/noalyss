begin;
alter table tags add column t_color int;
alter table tags alter t_color set default 1;
update tags set t_color=1;
create table operation_tag (opt_id bigserial primary key, jrn_id bigint , tag_id int);
alter table operation_tag add constraint opt_jrnx foreign key   (jrn_id)  references jrn (jr_id) on update cascade on delete cascade;
alter table operation_tag add constraint opt_tag_id foreign key   (tag_id)  references tags (t_id) on update cascade on delete cascade;
comment on table operation_tag is 'Tag attached to an operation';
ALTER TABLE public.user_filter ADD uf_tag text ;
alter table user_filter add uf_tag_option int;

COMMENT ON COLUMN public.user_filter.uf_tag IS 'Tag list';
COMMENT ON COLUMN public.user_filter.uf_tag_option IS '0 : all tags must be present, 1: at least one';
insert into version (val,v_description) values (151,'Tag with color and operation');
commit;
