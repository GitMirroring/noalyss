begin;
INSERT INTO public.document_option (do_code, document_type_id, do_enable, do_option) select 'detail_operation', dt_id, 1, 
case when dt_id in (2,3,4,5,21) then 'VEN' else 'ACH' end  do_option
from document_type 
where 
not exists (select 1 from document_option where document_type_id in (2,3,4,5,10,20) and do_code='detail_operation' )
and dt_id in (2,3,4,5,10,20,21);


INSERT INTO public.document_option (do_code, document_type_id, do_enable, do_option) select  'contact_multiple',dt_id , 1, NULL
from document_type where 
not exists (select 1 from document_option where document_type_id in (2,3,4,5,10,20) and do_code='contact_multiple' );

INSERT INTO public.document_option (do_code, document_type_id, do_enable, do_option) select 'make_invoice', dt_id, 1, NULL
from document_type 
where 
not exists (select 1 from document_option where document_type_id in (2,4) and do_code='make_invoice' )
and dt_id in (2,4);

insert into version (val,v_description) values (148,'Default values for document_option');
commit;
