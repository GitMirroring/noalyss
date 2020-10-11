begin;

ALTER TABLE public.document_option ADD do_option varchar NULL;
COMMENT ON COLUMN public.document_option.do_option IS 'Option for the detail';

alter table attr_def add column ad_search_followup int;
alter table attr_def alter column ad_search_followup set default 1;
update attr_def set ad_search_followup=1;
comment on column attr_def.ad_search_followup is '1 : search  available  from followup , 0  : search not available in followup';


create table jnt_document_option_contact
(
jdoc_id bigserial primary key,
jdoc_enable int not null, 
document_type_id bigint references document_type (dt_id) on delete cascade on update cascade,
contact_option_ref_id bigint references contact_option_ref(cor_id) on delete cascade  on update  cascade
);
ALTER TABLE public.jnt_document_option_contact ADD CONSTRAINT jnt_document_option_contact_un UNIQUE (document_type_id,contact_option_ref_id);
ALTER TABLE public.jnt_document_option_contact ADD CONSTRAINT jnt_document_option_contact_check CHECK (jdoc_enable in (0,1));

insert into attr_def(ad_id,ad_text,ad_type,ad_size) values (54,'Actif','check','1');
insert into attr_min(frd_id,ad_id) select frd_id , 54 from fiche_def_ref ;
insert into jnt_fic_attr  (fd_id,ad_id,jnt_order) select fd_id,54,30 from fiche_def;
insert into fiche_detail (f_id,ad_id,ad_value) select f_id, 54,1 from fiche_detail where ad_id=1 and f_id not in (select f_id from fiche_detail where ad_id=54);
insert into menu_ref(me_code,me_menu,me_file,me_type) values ('CSV:FollowUpContactOption','Export action suivi','export_follow_up_contact_csv.php','PR');
insert into profile_menu (me_code,p_id,p_type_display) select 'CSV:FollowUpContactOption',p_id,'P' from profile;



insert into version (val,v_description) values (146,'Export CSV for Multiple card, contact option by document type');
commit;
