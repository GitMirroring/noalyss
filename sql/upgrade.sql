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
