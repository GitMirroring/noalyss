begin;

INSERT INTO public.menu_ref
(me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue)
VALUES('CSV:Analytic_Axis', 'Export ANC', 'export_anc_axis_csv.php', NULL, 'Export ANC Liste comptes', NULL, NULL, 'PR', NULL);


insert into profile_menu (me_code,p_id,p_type_display) select   distinct   'CSV:Analytic_Axis',  p_id,  'P' from profile_menu
where
    p_id in (select distinct p_id from profile_menu where me_code='PLANANC');

insert into version (val,v_description) values (134,'Export CSV:Analytic_Axis');
commit ;