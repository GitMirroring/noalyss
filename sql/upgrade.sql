INSERT INTO public.menu_ref
(me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue)
VALUES('CFGCURRENCY', 'Conf. Devises', 'acc_currency_cfg.inc.php', NULL, 'Devises', NULL,NULL,'ME','Permet de configurer les devises');

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


insert into "parameter" values ('MY_CURRENCY','N');