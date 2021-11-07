begin;
INSERT INTO public.menu_ref (me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue) VALUES('PDF:AncAccList', 'Export Historique Compt. Analytique', 'export_anc_acc_list_pdf.php', NULL, NULL, NULL, NULL, 'PR', NULL);

insert into profile_menu (me_code,p_id,p_type_displayn,pm_default) select  'PDF:AncAccList',p_id,p_type_display,pm_default from profile_menu pm where me_code='CSV:AncAccList';
insert into version (val,v_description) values (170,'new : export in PDF balance Analytic / Accountancy');
commit ;