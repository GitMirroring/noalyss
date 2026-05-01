begin;

ALTER TABLE public.jrn_sup_document DROP CONSTRAINT jrn_sup_document_jrn_fk;
ALTER TABLE public.jrn_sup_document ADD CONSTRAINT jrn_sup_document_jrn_fk FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON DELETE CASCADE ON UPDATE CASCADE;

insert into version (val,v_description) values (208,'delete an operation + additional documents');
commit;