begin;

CREATE OR REPLACE FUNCTION isnumeric(text) RETURNS BOOLEAN AS $$
DECLARE x NUMERIC;
BEGIN
    x = $1::NUMERIC;
    RETURN TRUE;
EXCEPTION WHEN others THEN
    RETURN FALSE;
END;
$$
STRICT
LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION isdate(text,text) RETURNS BOOLEAN AS $$
DECLARE x timestamp;
BEGIN
    x := to_date($1,$2);
    RETURN TRUE;
EXCEPTION WHEN others THEN
    RETURN FALSE;
END;
$$
LANGUAGE plpgsql;

ALTER TABLE public.jrn_def ADD currency_id int NULL;
ALTER TABLE public.jrn_def ALTER COLUMN currency_id SET DEFAULT 0;
update  public.jrn_def  set currency_id = 0 ; 
ALTER TABLE public.jrn_def ALTER COLUMN currency_id SET NOT NULL;
ALTER TABLE public.jrn_def ADD CONSTRAINT jrn_def_currency_fk FOREIGN KEY (currency_id) REFERENCES public.currency(id);

COMMENT ON COLUMN public.jrn_def.currency_id IS 'Default currency for financial ledger';


alter table quant_fin add j_id bigint;

with j_fin as (
select jrnx.j_id,quant_fin.qf_id from quant_fin join jrn using (jr_id) join jrnx on (j_grpt=jr_grpt_id and f_id=qf_other)
)
update quant_fin set j_id =j_fin.j_id from j_fin where j_fin.qf_id=quant_fin.qf_id;

alter table quant_fin add constraint jrnx_j_id_fk foreign key (j_id ) references jrnx(j_id) on delete cascade on update cascade;


insert into version (val,v_description) values (131,'Currency : adapt quant_fin');
commit;
