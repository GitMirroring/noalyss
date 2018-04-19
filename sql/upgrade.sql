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
