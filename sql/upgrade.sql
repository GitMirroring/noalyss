
CREATE OR REPLACE FUNCTION comptaproc.jrn_check_periode()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
declare
bClosed bool;
str_status text;
ljr_tech_per jrn.jr_tech_per%TYPE;
ljr_def_id jrn.jr_def_id%TYPE;
lreturn jrn%ROWTYPE;
begin
if TG_OP='UPDATE' then
    ljr_tech_per :=OLD.jr_tech_per ;
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=NEW;
    if NEW.jr_date = OLD.jr_date then
        return NEW;
    end if;
    if comptaproc.is_closed(NEW.jr_tech_per,NEW.jr_def_id) = true then
              raise exception 'Periode fermee';
    end if;
end if;

if TG_OP='INSERT' then
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_tech_per :=NEW.jr_tech_per ;
    ljr_def_id :=NEW.jr_def_id;
    lreturn :=NEW;
end if;

if TG_OP='DELETE' then
    ljr_tech_per :=OLD.jr_tech_per;
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=OLD;
end if;

if comptaproc.is_closed (ljr_tech_per,ljr_def_id) = true then
       raise exception 'Periode fermee';
end if;

return lreturn;
end;
$function$;
LANGUAGE plpgsql;

-- New right for action : delete
ALTER TABLE public.user_sec_action_profile drop CONSTRAINT user_sec_action_profile_ua_right_check;
ALTER TABLE public.user_sec_action_profile ADD CONSTRAINT user_sec_action_profile_ua_right_check check (ua_right in ('R','W','X','O'));

