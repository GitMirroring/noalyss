begin;
 update menu_ref set me_menu = replace(me_menu,'Configuration','') where me_menu like 'Configuration%';

insert into version (val,v_description) values (160,'correct menu item');
commit ;