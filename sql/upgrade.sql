ALTER TABLE public.document_option ADD do_option varchar NULL;
COMMENT ON COLUMN public.document_option.do_option IS 'Option for the detail';

alter table attr_def add column ad_search_followup int;
alter table attr_def alter column ad_search_followup set default 1;
update attr_def set ad_search_followup=1;
comment on column attr_def.ad_search_followup is '1 : search  available  from followup , 0  : search not available in followup'


create table jnt_document_option_contact
(
jdoc_id bigserial primary key,
jdoc_enable int not null, 
document_type_id bigint references document_type (dt_id) on delete cascade on update cascade,
contact_option_ref_id bigint references contact_option_ref(cor_id) on delete cascade  on update  cascade
);
ALTER TABLE public.jnt_document_option_contact ADD CONSTRAINT jnt_document_option_contact_un UNIQUE (document_type_id,contact_option_ref_id);
ALTER TABLE public.jnt_document_option_contact ADD CONSTRAINT jnt_document_option_contact_check CHECK (jdoc_enable in (0,1));
