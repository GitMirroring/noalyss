begin;


insert into menu_ref (me_code,me_menu,me_file,me_description,me_type,me_description_etendue)
values('PCUR01','Devise','print_currency01.inc.php','Résumé par devise','ME','Résumé par devise afin de 
faire de calculer les écarts de conversion (différence de change) pour les actifs et passifs');

insert into profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep)
select me_code,'PRINT',1,260,'E',0,6 from menu_ref where me_code='PCUR01'
union
select me_code,'PRINT',1,260,'E',0,35 from menu_ref where me_code='PCUR01'
union
select me_code,'PRINT',2,260,'E',0,719 from menu_ref where me_code='PCUR01' and exists (select 1 from profile where p_id=2)
union
select me_code,'PRINT',2,260,'E',0,716 from menu_ref where me_code='PCUR01' and exists (select 1 from profile where p_id=2)
;

insert into menu_ref (me_code,me_menu,me_file,me_type)
values ('CSV:pcur01','Export Devise CSV','export_pcur01_csv.php','PR'),
       ('PDF:pcur01','Export Devise PDF','export_pcur01_pdf.php','PR')
;


insert into version (val,v_description) values (157,'new feature Currency search');
commit ;

