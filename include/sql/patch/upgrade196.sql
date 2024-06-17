begin;
with correct_periode as (select jr_tech_per, jr_grpt_id from jrn)
update jrnx set j_tech_per = jr_tech_per from correct_periode where correct_periode.jr_grpt_id=j_grpt and correct_periode.jr_tech_per != j_tech_per;



ALTER TABLE public.tva_rate ADD tva_code text ;
ALTER TABLE public.tva_rate ADD CONSTRAINT tva_code_unique UNIQUE (tva_code);
update tva_rate set tva_code=null;


drop VIEW public.v_tva_rate;

CREATE OR REPLACE VIEW public.v_tva_rate
AS SELECT tva_rate.tva_id,
          tva_rate.tva_rate,
          tva_rate.tva_code,
          tva_rate.tva_label,
          tva_rate.tva_comment,
          split_part(tva_rate.tva_poste, ','::text, 1) AS tva_purchase,
          split_part(tva_rate.tva_poste, ','::text, 2) AS tva_sale,
          tva_rate.tva_both_side,
          tva_rate.tva_payment_purchase,
          tva_rate.tva_payment_sale
   FROM tva_rate;

CREATE OR REPLACE FUNCTION update_tva_code ()
    returns int4
AS
$BODY$
declare
/*
 * Section for variables
 */
    counter int:=0;
    letter int;
    x record;
    e record;
    str_tva_code text;
begin
    -- basic loop
    for x in select distinct tva_rate  from public.tva_rate order by tva_rate
        loop
            letter :=65;
            for e in select * from public.tva_rate where tva_rate = x.tva_rate loop
                    str_tva_code := round(e.tva_rate*1000)::text||chr(letter);
                    update tva_rate set tva_code=str_tva_code where tva_id=e.tva_id;
                    letter := letter+1;
                    counter := counter+1;
                end loop;

        end loop;
    return counter;
end;
$BODY$
LANGUAGE plpgsql;

select update_tva_code();

drop function update_tva_code();
alter table public.tva_rate alter tva_code set not null;

ALTER TABLE public.op_predef_detail ALTER COLUMN opd_tva_id TYPE text USING opd_tva_id::text;
insert into version (val,v_description) values (197,'Adapt for VAT CODE');
commit;
