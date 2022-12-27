begin;

COMMENT ON COLUMN public.tva_rate.tva_both_side IS 'If set to 1 , the amount VAT will be reversed (autoliquidation)';
COMMENT ON COLUMN public.tva_rate.tva_poste IS 'accounting';
COMMENT ON COLUMN public.tva_rate.tva_comment IS 'Description of VAT';
COMMENT ON COLUMN public.tva_rate.tva_rate IS 'Rate';
COMMENT ON COLUMN public.tva_rate.tva_label IS 'Label';
alter table quant_purchase drop constraint qp_vat_code_fk;
alter table quant_purchase add constraint qp_vat_code_fk foreign key (qp_vat_code) references tva_rate(tva_id) on update cascade ;

alter table quant_sold drop constraint qs_vat_code_fk;
alter table quant_sold add constraint qs_vat_code_fk foreign key (qs_vat_code) references tva_rate(tva_id) on update cascade ;

insert into version (val,v_description) values (183,'Mantis #1327 code for vat');
commit;