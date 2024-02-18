begin;


CREATE OR REPLACE FUNCTION comptaproc.trg_remove_script_tag()
    RETURNS trigger
    LANGUAGE plpgsql
AS $function$

begin

    NEW.agc_comment_raw:= regexp_replace(NEW.agc_comment_raw, '<script', 'scritp', 'i');
    return NEW;

end;
$function$;


create trigger t_remove_script_tag before
    insert
    or
    update
    on
        public.action_gestion_comment for each row execute function comptaproc.trg_remove_script_tag();

INSERT INTO public.menu_ref (me_code,me_menu,me_file,me_url,me_description,me_parameter,me_javascript,me_type,me_description_etendue) VALUES
    ('PDF:card','export Fiche détail PDF','export_card_pdf.php',NULL,NULL,NULL,NULL,'PR',NULL);

insert into public.profile_menu (me_code,p_id,p_type_display) select 'PDF:card',p_id,'P' from profile;

create table action_gestion_filter(af_id bigint generated always as identity, af_user text not null, af_name text not null , af_search text not null);



insert into version (val,v_description) values (195,'Protect injection JS , sauve recherche suivi');
commit;