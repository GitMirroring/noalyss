begin;
update menu_ref set me_menu='Profil' , me_description_etendue='Configuration des profils des utilisateurs, permet de fixer les journaux, profils dans les documents et stock que  ce profil peut utiliser. Cela limite les utilisateurs puisque ceux-ci ont un profil',
me_description='Configuration profil' where me_code='CFGPRO';


insert into version (val,v_description) values (163,'typo in menu');
commit ;