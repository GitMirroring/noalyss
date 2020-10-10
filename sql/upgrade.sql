ALTER TABLE public.document_option ADD do_option varchar NULL;
COMMENT ON COLUMN public.document_option.do_option IS 'Option for the detail';

alter table attr_def add column ad_search_followup int;
alter table attr_def alter column ad_search_followup set default 1;
update attr_def set ad_search_followup=1;
comment on column attr_def.ad_search_followup is '1 : search  available  from followup , 0  : search not available in followup'
