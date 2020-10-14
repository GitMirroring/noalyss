begin;
INSERT INTO public.document_option (do_code, document_type_id, do_enable, do_option) select 'followup_comment', dt_id, 1, NULL
from document_type ;

insert into version (val,v_description) values (149,'Default values for document_option,comment on followup');
commit;
