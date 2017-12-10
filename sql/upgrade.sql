

alter table action_gestion drop ag_ref_ag_id;
/* --- repository 
-- add style
insert into theme (the_name,the_filestyle) values ('Classic 692','style-r692.css');

-- add constraint
alter table jnt_use_dos add CONSTRAINT use_id_dos_id_uniq UNIQUE (use_id,dos_id);
*/
create sequence tmp_pcmn_id_seq;
ALTER TABLE tmp_pcmn ADD COLUMN id bigint;
update tmp_pcmn set id=nextval('tmp_pcmn_id_seq');

ALTER TABLE tmp_pcmn ALTER COLUMN id SET NOT NULL;
ALTER TABLE tmp_pcmn ALTER COLUMN id SET DEFAULT nextval('tmp_pcmn_id_seq'::regclass);
ALTER TABLE tmp_pcmn   ADD CONSTRAINT id_ux UNIQUE(id);
COMMENT ON COLUMN tmp_pcmn.id IS 'allow to identify the row, it is unique and not null (pseudo pk)';

-- set search_path to public,comptaproc;
alter table tmp_pcmn add column pcm_direct_use varchar(1);
COMMENT ON COLUMN tmp_pcmn.pcm_direct_use IS 'Value are N or Y , N cannot be used directly , not even through a card';
ALTER TABLE tmp_pcmn ALTER COLUMN pcm_direct_use  SET DEFAULT 'Y';
update tmp_pcmn set pcm_direct_use='Y';
update tmp_pcmn set pcm_direct_use='N' where length(pcm_val) < 3 and not exists (select j_poste from jrnx where j_poste=pcm_val);
ALTER TABLE tmp_pcmn ALTER COLUMN pcm_direct_use SET NOT NULL;
alter table tmp_pcmn add constraint pcm_direct_use_ck check (pcm_direct_use in ('Y','N'));

insert into bilan (b_name,b_file_template,b_file_form,b_type) values ('ASBL','document/fr_be/bnb-asbl.rtf','document/fr_be/bnb-asbl.form','RTF');

alter table jnt_letter drop jl_amount_deb;

ALTER TABLE operation_analytique ADD COLUMN f_id bigint;
ALTER TABLE operation_analytique  ADD CONSTRAINT operation_analytique_fiche_id_fk FOREIGN KEY (f_id)       REFERENCES fiche (f_id) MATCH SIMPLE       ON UPDATE cascade ON cascade;
COMMENT ON COLUMN operation_analytique.f_id IS 'FK to fiche.f_id , used only with ODS';

drop FUNCTION comptaproc.table_analytic_account(text,text);
drop FUNCTION comptaproc.table_analytic_card(text,text);

CREATE TABLE public.user_filter (
	id bigserial,
	login text NULL,
	nb_jrn int4 NULL,
	date_start varchar(10) NULL,
	date_end varchar(10) NULL,
	description text NULL,
	amount_min numeric(20,4) NULL,
	amount_max numeric(20,4) NULL,
	qcode text NULL,
	accounting text NULL,
	r_jrn text NULL,
	date_paid_start varchar(10) NULL,
	date_paid_end varchar(10) NULL,
	ledger_type varchar(5) NULL,
	all_ledger int4 NULL,
	filter_name text NOT NULL,
	unpaid varchar NULL,
	PRIMARY KEY (id)
);
