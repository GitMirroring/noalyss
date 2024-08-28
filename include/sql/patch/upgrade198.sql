begin;
CREATE OR REPLACE FUNCTION  replace_menu_code( code_source text, code_destination text)
    RETURNS void
AS $function$
begin
    /*code */

    update bookmark set b_action = replace(b_action,code_source,code_destination) where b_action ~ code_source;
    update menu_ref set me_code =code_destination where me_code = code_source;
    update profile_menu set me_code=code_destination where me_code = code_source;
    update profile_menu set me_code_dep=code_destination where me_code_dep = code_source;
end ;
$function$
    LANGUAGE plpgsql;


select replace_menu_code('PRINTGL','P0GRL');
select replace_menu_code('PRINTBAL','P0BAL');
select replace_menu_code('PRINTREC','P0RAP');
select replace_menu_code('PRINTBILAN','P0BIL');
select replace_menu_code('PRINTJRN','P0JRN');
select replace_menu_code('PRINTTVA','P0TVA');
select replace_menu_code('PRINTPOSTE','P0PST');
select replace_menu_code('PRINTREPORT','P0RPO');
select replace_menu_code('BALAGE','P0BLG');


insert into menu_ref (me_code,me_menu,me_file,me_description,me_type,me_description_etendue)
values('P1TVA','Détail TVA','tax_detail.inc.php','Détail TVA  par journal','ME','Détail des TVA ');

insert into profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep)
select me_code,'PRINT',1,255,'E',0,6 from menu_ref where me_code='P1TVA'
union
select me_code,'PRINT',1,255,'E',0,35 from menu_ref where me_code='P1TVA'
union
select me_code,'PRINT',2,255,'E',0,719 from menu_ref where me_code='P1TVA' and exists (select 1 from profile where p_id=2)
union
select me_code,'PRINT',2,255,'E',0,716 from menu_ref where me_code='P1TVA' and exists (select 1 from profile where p_id=2)
;
insert into menu_ref (me_code,me_menu,me_file,me_type)
values ('CSV:p1tva','Export Détail TVA','export_p1tva_csv.php','PR')

;

insert into profile_menu(me_code,p_id,p_type_display) select 'CSV:p1tva',p_id,'P' from profile where p_id in (1,2);


insert into version (val,v_description) values (199,'Detail VAT');
commit;