begin;
ALTER TABLE public.tva_rate ADD tva_payment_sale char(1) NULL DEFAULT 'O';
update public.tva_rate set tva_payment_sale='O';
ALTER TABLE public.tva_rate ADD CONSTRAINT tva_rate_sale_check CHECK (tva_payment_sale in ('O','P'));
comment on column public.tva_rate.tva_payment_sale is 'Check if the VAT on Sale  must be declared when at the date of payment (P) or the date of operation (O)';


ALTER TABLE public.tva_rate ADD tva_payment_purchase char(1) NULL DEFAULT 'O';
update public.tva_rate set tva_payment_purchase='O';
ALTER TABLE public.tva_rate ADD CONSTRAINT tva_rate_purchase_check CHECK (tva_payment_purchase in ('O','P'));
comment on column public.tva_rate.tva_payment_purchase is 'Check if the VAT on Purchase must be declared when at the date of payment (P) or the date of operation (O)';


drop VIEW public.v_tva_rate;

CREATE OR REPLACE VIEW public.v_tva_rate
AS SELECT tva_rate.tva_id,
    tva_rate.tva_rate,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    split_part(tva_rate.tva_poste, ','::text, 1) AS tva_purchase,
    split_part(tva_rate.tva_poste, ','::text, 2) AS tva_sale,
    tva_rate.tva_both_side,
    tva_payment_purchase,
    tva_payment_sale
   FROM tva_rate;

COMMENT ON VIEW public.v_tva_rate IS 'Show this table to be easily used by  Tva_Rate_MTable';
COMMENT ON COLUMN public.v_tva_rate.tva_purchase IS ' VAT used for purchase';
COMMENT ON COLUMN public.v_tva_rate.tva_sale IS ' VAT used for sale';
COMMENT ON COLUMN public.v_tva_rate.tva_both_side IS 'if 1 ,  VAT avoided ';
comment on column public.v_tva_rate.tva_payment_purchase is 'Check if the VAT on Purchase must be declared when at the date of payment (P) or the date of operation (O)';
comment on column public.v_tva_rate.tva_payment_sale is 'Check if the VAT on Sale must be declared when at the date of payment (P) or the date of operation (O)';

insert into version (val,v_description) values (139,'Add VAT exigibility');
commit ;