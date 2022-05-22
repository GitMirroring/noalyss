-- auto-generated definition
create table acc_other_tax
(
    ac_id             serial        constraint acc_other_tax_pk            primary key,
    ac_label       text         not null,
    ac_rate numeric (5,2) not null,
    ajrn_def_id    integer[],
    ac_accounting account_type not null
);
comment on table acc_other_tax is 'Additional tax for Sale or Purchase ';
comment on column acc_other_tax.ac_label is 'Label of the tax';
comment on column acc_other_tax.ac_rate is 'rate of the tax in percent';
comment on column acc_other_tax.ajrn_def_id is 'array of to FK jrn_def (jrn_def_id)';
comment on column acc_other_tax.ac_accounting is 'FK tmp_pcmn (pcm_val)';


ALTER TABLE public.jrn drop CONSTRAINT jrn_pkey ;
ALTER TABLE public.jrn ADD CONSTRAINT jrn_pkey PRIMARY KEY (jr_id);

-- public.jrn_tax definition

-- Drop table

-- DROP TABLE public.jrn_tax;

CREATE TABLE public.jrn_tax (
                                jt_id int4 NOT NULL GENERATED ALWAYS AS IDENTITY,
                                j_id int8 NOT NULL, -- fk jrnx
                                pcm_val public."account_type" NOT NULL, -- FK tmp_pcmn
                                ac_id int4 NOT NULL, -- FK to acc_other_tax
                                CONSTRAINT jrn_tax_pk PRIMARY KEY (jt_id)
);

-- Column comments

COMMENT ON COLUMN public.jrn_tax.j_id IS 'fk jrnx';
COMMENT ON COLUMN public.jrn_tax.pcm_val IS 'FK tmp_pcmn';
COMMENT ON COLUMN public.jrn_tax.ac_id IS 'FK to acc_other_tax';


-- public.jrn_tax foreign keys

ALTER TABLE public.jrn_tax ADD CONSTRAINT jrn_tax_acc_other_tax_fk FOREIGN KEY (ac_id) REFERENCES public.acc_other_tax(ac_id);
ALTER TABLE public.jrn_tax ADD CONSTRAINT jrn_tax_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id);
