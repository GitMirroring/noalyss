begin;
INSERT INTO public.menu_ref
(me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue)
VALUES('CFGCURRENCY', 'Devises', 'acc_currency_cfg.inc.php', NULL, 'Configuration des devises', NULL,NULL,'ME','Permet de configurer les devises');

INSERT INTO public.profile_menu
(pm_id, me_code, me_code_dep, p_id, p_order, p_type_display, pm_default, pm_id_dep)
VALUES(nextval('profile_menu_pm_id_seq'), 'CFGCURRENCY', 'PARAM', 1, 50, 'E', 0, 45);

-- Drop table

-- DROP TABLE public.currency

CREATE TABLE public.currency (
	id serial NOT NULL,
	cr_code_iso varchar(10) NULL,
	CONSTRAINT currency_pk PRIMARY KEY (id),
	CONSTRAINT currency_un UNIQUE (cr_code_iso)
);


-- Drop table

-- DROP TABLE public.currency_history

CREATE TABLE public.currency_history (
	id serial NOT NULL,
	ch_value numeric(20,6) NOT NULL,
	ch_from date NOT NULL,
	currency_id int4 NOT NULL,
	CONSTRAINT currency_history_pk PRIMARY KEY (id),
	CONSTRAINT currency_history_currency_fk FOREIGN KEY (currency_id) REFERENCES currency(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
)
;

-- Ajouter commentaire sur colonne

ALTER TABLE public.currency ADD cr_name varchar(80) NULL;
insert into currency (id,cr_code_iso,cr_name) values (0,'EUR','EUR');
insert into currency_history (ch_value,ch_from,currency_id) values (1,to_date('01.01.2000','DD.MM.YYYY'),0);

ALTER TABLE public.currency_history ADD CONSTRAINT currency_history_check CHECK (ch_value > 0) ;

-- Create view to manage the table
create view v_currency_last_value as 
with recent_rate as 
( select 
	currency_id,max(ch_from) as rc_from
	from 
	 currency_history 
	 group by currency_id
	 )
select 
	cr1.id as currency_id,
	cr1.cr_name,
	cr1.cr_code_iso,
	ch1.id as currency_history_id,
	ch1.ch_value as ch_value,
	to_char(rc_from,'DD.MM.YYYY') as str_from 
from
currency as cr1
join recent_rate on (currency_id=cr1.id)
join currency_history as ch1 on (recent_rate.currency_id=ch1.currency_id and rc_from=ch1.ch_from);

COMMENT ON COLUMN public.currency_history.id IS 'pk' ;
COMMENT ON COLUMN public.currency_history.ch_value IS 'rate of currency depending of currency of the folder' ;
COMMENT ON COLUMN public.currency_history.ch_from IS 'Date when the rate is available' ;
COMMENT ON COLUMN public.currency_history.currency_id IS 'FK to currency' ;
COMMENT ON COLUMN public.currency.cr_code_iso IS 'Code ISO' ;
COMMENT ON COLUMN public.currency.cr_name IS 'Name of the currency' ;



-- Drop table

-- DROP TABLE public.operation_currency

CREATE TABLE public.operation_currency (
	id bigserial NOT NULL,
	oc_amount numeric(20,6) NOT NULL, -- amount in currency
	oc_vat_amount numeric(20,6) NULL DEFAULT 0, -- vat amount in currency
	oc_price_unit numeric(20,6) NULL, -- unit price in currency
	j_id int8 NOT NULL, -- fk to jrnx
	CONSTRAINT operation_currency_pk PRIMARY KEY (id)
);

ALTER TABLE public.operation_currency ADD CONSTRAINT operation_currency_jrnx_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON DELETE CASCADE ON UPDATE CASCADE;

-- Column comments

COMMENT ON COLUMN public.operation_currency.oc_amount IS 'amount in currency' ;
COMMENT ON COLUMN public.operation_currency.oc_vat_amount IS 'vat amount in currency' ;
COMMENT ON COLUMN public.operation_currency.oc_price_unit IS 'unit price in currency' ;
COMMENT ON COLUMN public.operation_currency.j_id IS 'fk to jrnx' ;
alter table jrn add currency_id bigint default 0;
update jrn set currency_id=0;
alter table jrn add currency_rate numeric (20,6) default 1;
update jrn set currency_rate=1;
alter table jrn add currency_rate_ref numeric(20,6) default 1;
update jrn set currency_rate_ref=1;
ALTER TABLE public.jrn ADD CONSTRAINT jrn_currency_fk FOREIGN KEY (currency_id) REFERENCES public.currency(id) ON DELETE RESTRICT ON UPDATE RESTRICT;

CREATE OR REPLACE FUNCTION comptaproc.insert_quant_purchase(p_internal text, p_j_id numeric, p_fiche character varying, p_quant numeric, p_price numeric, p_vat numeric, p_vat_code integer, p_nd_amount numeric, p_nd_tva numeric, p_nd_tva_recup numeric, p_dep_priv numeric, p_client character varying, p_tva_sided numeric, p_price_unit numeric)
 RETURNS void
AS $function$
declare
        fid_client integer;
        fid_good   integer;
        account_priv    account_type;
        fid_good_account account_type;
        n_dep_priv numeric;
begin
        n_dep_priv := p_dep_priv;
        select p_value into account_priv from parm_code where p_code='DEP_PRIV';
        select f_id into fid_client from
                fiche_detail where ad_id=23 and ad_value=upper(trim(p_client));
        select f_id into fid_good from
                 fiche_detail where ad_id=23 and ad_value=upper(trim(p_fiche));
        select ad_value into fid_good_account from fiche_detail where ad_id=5 and f_id=fid_good;
        if strpos( fid_good_account , account_priv ) = 1 then
                n_dep_priv=p_price;
        end if; 
            
        insert into quant_purchase
                (qp_internal,
                j_id,
                qp_fiche,
                qp_quantite,
                qp_price,
                qp_vat,
                qp_vat_code,
                qp_nd_amount,
                qp_nd_tva,
                qp_nd_tva_recup,
                qp_supplier,
                qp_dep_priv,
                qp_vat_sided,
                qp_unit)
        values
                (p_internal,
                p_j_id,
                fid_good,
                p_quant,
                p_price,
                p_vat,
                p_vat_code,
                p_nd_amount,
                p_nd_tva,
                p_nd_tva_recup,
                fid_client,
                n_dep_priv,
                p_tva_sided,
                p_price_unit);
        return;
end;
$function$
LANGUAGE plpgsql;

insert into version (val,v_description) values (129,'Currency : create view , create tables ');
commit;