alter table action_gestion drop ag_ref_ag_id;
--- repository insert into theme (the_name,the_filestyle) values ('Classic 692','style-r692.css');

create sequence tmp_pcmn_id_seq;
ALTER TABLE tmp_pcmn ADD COLUMN id bigint;
update tmp_pcmn set id=nextval('tmp_pcmn_id_seq');

ALTER TABLE tmp_pcmn ALTER COLUMN id SET NOT NULL;
ALTER TABLE tmp_pcmn ALTER COLUMN id SET DEFAULT nextval('tmp_pcmn_id_seq'::regclass);
ALTER TABLE tmp_pcmn   ADD CONSTRAINT id_ux UNIQUE(id);
COMMENT ON COLUMN tmp_pcmn.id IS 'allow to identify the row, it is unique and not null (pseudo pk)';


insert into bilan (b_name,b_file_template,b_file_form,b_type) values ('ASBL','document/fr_be/bnb-asbl.rtf','document/fr_be/bnb-asbl.form','RTF');

alter table jnt_letter drop jl_amount_deb;

ALTER TABLE operation_analytique ADD COLUMN f_id bigint;
ALTER TABLE operation_analytique  ADD CONSTRAINT operation_analytique_fiche_id_fk FOREIGN KEY (f_id)       REFERENCES fiche (f_id) MATCH SIMPLE       ON UPDATE cascade ON cascade;
COMMENT ON COLUMN operation_analytique.f_id IS 'FK to fiche.f_id , used only with ODS';

drop FUNCTION comptaproc.table_analytic_account(text,text);
drop FUNCTION comptaproc.table_analytic_card(text,text);