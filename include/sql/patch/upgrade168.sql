begin;

update jrn set currency_id = 0,currency_rate=1,currency_rate_ref = 1  where currency_id  is null or currency_rate is null or currency_rate_ref is null;


    
   
CREATE OR REPLACE FUNCTION comptaproc.jrn_currency()
 RETURNS trigger
 AS $function$
begin 
	if new.currency_id is null then 
		new.currency_id := 0;
                new.currency_rate := 1;
                new.currency_rate_ref := 1;
	end if;
	return new;
end;
$function$
LANGUAGE plpgsql;

create trigger t_jrn_currency before
insert or update
    on
    public.jrn for each row execute procedure comptaproc.jrn_currency();


ALTER TABLE public.jrn ALTER COLUMN currency_id SET NOT NULL;
ALTER TABLE public.jrn ALTER COLUMN currency_rate SET NOT NULL;
ALTER TABLE public.jrn ALTER COLUMN currency_rate_ref SET NOT NULL;


insert into version (val,v_description) values (169,'Fix bug currency_id is null, from IMPORTBANK');
commit ;
