CREATE OR REPLACE FUNCTION comptaproc.trg_remove_script_tag()
    RETURNS trigger
    LANGUAGE plpgsql
AS $function$

begin

    NEW.agc_comment_raw:= regexp_replace(NEW.agc_comment_raw, '<script', 'scritp', 'i');
    return NEW;

end;
$function$
;


create trigger t_remove_script_tag before
    insert
    or
    update
    on
        public.action_gestion_comment for each row execute function comptaproc.trg_remove_script_tag();


