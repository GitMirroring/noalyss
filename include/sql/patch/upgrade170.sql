begin;
update menu_ref set me_code='CMCARD' where me_code='CFGCARDCAT';
update menu_ref set me_code='CCARD' where me_code='CFGCARD';
update menu_ref set me_code='CCARDAT' where me_code='CFGATCARD';
update MENU_REF set ME_MENU='Modèle de fiches' , me_description ='Gestion de modèle de fiches',me_description_etendue ='Permet de changer le poste comptable de base des modèles de fiches' where me_code='CMCARD';
update BOOKMARK set b_action=replace(b_action,'CFGCARDCAT','CMCARD');
update BOOKMARK set b_action=replace(b_action,'CFGCARD','CCARD');
update BOOKMARK set b_action=replace(b_action,'CFGATCARD','CCARDAT');


insert into version (val,v_description) values (171,'change name for menu ');
commit;