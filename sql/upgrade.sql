update menu_ref set me_menu = 'Dépôt' where me_code='CFGSTOCK';
update menu_ref set me_menu = 'Extension' where me_code='CFGPLUGIN';
update menu_ref set me_menu = 'Etiquette' where me_code='CFGTAG';

insert into menu_ref (me_code,me_description,me_description_etendue,me_type,me_menu) 
	values ('MCARD','Paramètrage des fiches','Menu regroupant ce qui concerne les fiches','E','Fiche'),
	 ('MDOC','Paramètrage des documents','Meu regroupant ce qui concene les documents ','E','Document'),
	 ('MACC','Paramètrage comptabilité','Menu regroupant ce qui concerne la comptabilité','E','Comptabilité');

insert into profile_menu(me_code,me_code_dep,p_id,p_order,p_type_display,pm_id_dep) 
select 'MCARD','PARAM',1,20,'E',(select min(pm_id) from profile_menu where me_code='PARAM' and p_id=1) ;


insert into profile_menu(me_code,me_code_dep,p_id,p_order,p_type_display,pm_id_dep) 
select 'MDOC','PARAM',1,30,'E',(select min(pm_id) from profile_menu where me_code='PARAM' and p_id=1) ;

insert into profile_menu(me_code,me_code_dep,p_id,p_order,p_type_display,pm_id_dep) 
select 'MACC','PARAM',1,50,'E',(select min(pm_id) from profile_menu where me_code='PARAM' and p_id=1) ;

update profile_menu set me_code_dep='MCARD',pm_id_dep=(select pm_id from profile_menu where me_code='MCARD') where  p_id=1 and me_code_dep ='PARAM' and me_code in ('CFGCARD');

update profile_menu set me_code_dep='MCARD',pm_id_dep=(select pm_id from profile_menu where me_code='MCARD') where  p_id=1 and me_code_dep ='DIVPARM' and me_code in ('CFGOPT1','CFGATCARD','CFGCARDCAT');


update profile_menu set me_code_dep='MDOC',pm_id_dep=(select pm_id from profile_menu where me_code='MDOC') where  p_id=1 and me_code_dep ='PARAM' and me_code in ('CFGDOC');

update profile_menu set me_code_dep='MDOC',pm_id_dep=(select pm_id from profile_menu where me_code='MDOC') where  p_id=1 and me_code_dep ='DIVPARM' and me_code in ('CFGDOCST','CFGACTION');

update profile_menu set me_code_dep='MACC',pm_id_dep=(select pm_id from profile_menu where me_code='MACC') where  p_id=1 and me_code_dep ='PARAM' and me_code in ('CFGPCMN','CFGCURRENCY','PERIODE','CFGLED','PREDOP');

update profile_menu set me_code_dep='MACC',pm_id_dep=(select pm_id from profile_menu where me_code='MACC') where  p_id=1 and me_code_dep ='DIVPARM' and me_code in ('CFGPAY','CFGTVA','CFGACC');

delete from profile_menu where p_id=1 and me_code='DIVPARM' and me_code_dep ='PARAM';
