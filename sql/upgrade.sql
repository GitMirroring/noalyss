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
	ch_value numeric(6) NOT NULL,
	ch_from timestamp NOT NULL,
	currency_id int4 NOT NULL,
	CONSTRAINT currency_history_pk PRIMARY KEY (id),
	CONSTRAINT currency_history_currency_fk FOREIGN KEY (id) REFERENCES currency(id)
)
;

-- Ajouter commentaire sur colonne

ALTER TABLE public.currency ADD cr_name varchar(80) NULL;

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
	rc_from 
from
currency as cr1
join recent_rate on (currency_id=cr1.id)
join currency_history as ch1 on (recent_rate.currency_id=ch1.currency_id and rc_from=ch1.ch_from);