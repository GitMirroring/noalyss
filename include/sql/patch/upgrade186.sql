begin;

-- more precision for currency conversion

DROP VIEW public.v_currency_last_value;

ALTER TABLE public.currency_history ALTER COLUMN ch_value TYPE numeric(20, 8) USING ch_value::numeric;


CREATE OR REPLACE VIEW public.v_currency_last_value
            AS WITH recent_rate AS (
       SELECT currency_history.currency_id,
           max(currency_history.ch_from) AS rc_from
    FROM currency_history
    GROUP BY currency_history.currency_id
)
SELECT cr1.id AS currency_id,
     cr1.cr_name,
     cr1.cr_code_iso,
     ch1.id AS currency_history_id,
     ch1.ch_value,
     to_char(recent_rate.rc_from::timestamp with time zone, 'DD.MM.YYYY'::text) AS str_from
FROM currency cr1
    JOIN recent_rate ON recent_rate.currency_id = cr1.id
    JOIN currency_history ch1 ON recent_rate.currency_id = ch1.currency_id AND recent_rate.rc_from = ch1.ch_from;

insert into version (val,v_description) values (187,'Currency : more decimals');
commit;