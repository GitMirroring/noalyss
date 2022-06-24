begin;

ALTER TABLE public.jrn_tax drop CONSTRAINT jrn_tax_fk ;
ALTER TABLE public.jrn_tax ADD CONSTRAINT jrn_tax_fk FOREIGN KEY (j_id) REFERENCES jrnx(j_id) on delete cascade on update cascade;

insert into version (val,v_description) values (174,'Supplemental tax : delete');
commit;