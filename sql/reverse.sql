drop trigger t_remove_script_tag on public.action_gestion_comment ;
drop FUNCTION comptaproc.trg_remove_script_tag();


delete from profile_menu where me_code='PDF:card';
delete from public.menu_ref where me_code='PDF:card';



drop table action_gestion_filter;
