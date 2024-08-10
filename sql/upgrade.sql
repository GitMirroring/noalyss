alter table attr_min add ad_default_order int;

update attr_min set ad_default_order = a1.ad_default_order from attr_def a1 where a1.ad_id = attr_min.ad_id;


select replace_menu_code('CFGACC','C0PST');
select replace_menu_code('CFGDOC','C0DOC');
select replace_menu_code('CFGSEC','C0SEC');
select replace_menu_code('CFGPCMN','C0PCMN');
select replace_menu_code('CFGPRO','C0PROFL');
select replace_menu_code('CFGLED','C0JRN');
select replace_menu_code('CFGDOCST','C1DOC');
select replace_menu_code('CFGDEFMENU','C0MENU');
select replace_menu_code('CFGPAY','C0PAY');
select replace_menu_code('CFGCURRENCY','C0DEV');
select replace_menu_code('CFGACTION','C0ACT');
select replace_menu_code('CFGOPT1','C0OPT1');
select replace_menu_code('CFGSTOCK','C0STOCK');
select replace_menu_code('CFGPLUGIN','C0PLG');
select replace_menu_code('CFGTAG','C0TAG');

update menu_ref set me_description ='Configuration des extensions' where me_code='C0PLG';
update menu_ref set me_description ='Clef de répartition pour la comptabilité analytique' where me_code='ANCKEY';
