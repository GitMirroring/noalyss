

-- protect against wrong card in fiche_detail

CREATE OR REPLACE FUNCTION comptaproc.fiche_detail_check_qcode()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
declare
	i record;
begin
	if NEW.ad_id=23 and NEW.ad_value != OLD.ad_value then
		update jrnx set j_qcode=NEW.ad_value where j_qcode = OLD.ad_value;
	        update op_predef_detail set opd_poste=NEW.ad_value where opd_poste=OLD.ad_value;
		for i in select ad_id from attr_def where ad_type = 'card' or ad_id=25 loop
			update fiche_detail set ad_value=NEW.ad_value where ad_value=OLD.ad_value and ad_id=i.ad_id;
			if i.ad_id=19 then
				update stock_goods set sg_code=NEW.ad_value where sg_code=OLD.ad_value;
			end if;

		end loop;
	end if;
return NEW;
end;
$function$
;

drop trigger if exists fiche_detail_check_qcode_trg on public.fiche_detail ;
drop function  comptaproc.fiche_detail_qcode_upd();

create trigger fiche_detail_check_qcode_trg before insert
or update on
public.fiche_detail for each row execute function comptaproc.fiche_detail_check_qcode();



update fiche_detail set ad_value=ad_value where ad_id in (select ad_id from attr_def where ad_type='card');

insert into parameter values ('MY_REPORT','N') ON CONFLICT DO NOTHING;

