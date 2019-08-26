begin;

insert into menu_ref (me_code,me_menu,me_file,me_description,me_type,me_description_etendue)
values('PRINTTVA','Résumé TVA','tax_summary.inc.php','totaux  par TVA et par journal','ME','Calcul des totaux  par TVA et par journal');

insert into profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep)
select me_code,'PRINT',1,250,'E',0,6 from menu_ref where me_code='PRINTTVA'
union
select me_code,'PRINT',1,250,'E',0,35 from menu_ref where me_code='PRINTTVA'
union
select me_code,'PRINT',2,250,'E',0,719 from menu_ref where me_code='PRINTTVA' and exists (select 1 from profile where p_id=2)
union
select me_code,'PRINT',2,250,'E',0,716 from menu_ref where me_code='PRINTTVA' and exists (select 1 from profile where p_id=2)
;
insert into menu_ref (me_code,me_menu,me_file,me_type)
values ('CSV:printtva','Export Résumé TVA','export_printtva_csv.php','PR'),
       ('PDF:printtva','Export Résumé TVA','export_printtva_pdf.php','PR')
;

insert into profile_menu(me_code,p_id,p_type_display) select 'CSV:printtva',p_id,'P' from profile where p_id in (1,2) union all select 'PDF:printtva',p_id,'P' from profile where p_id in (1,2);

insert into version (val,v_description) values (136,'new feature PRINTTVA');
commit ;