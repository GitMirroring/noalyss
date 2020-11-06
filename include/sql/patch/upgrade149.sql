begin;
update menu_ref set me_code='CFGOPT1',me_menu='Option Fiches', me_description = 'configure les options pour les fiches dans le suivi' where me_code='CFGCONTACT';
update bookmark set b_action=replace(b_action,'CFGCONTACT','CFGOPT1');

insert into version (val,v_description) values (150,'Change name default action');
commit;
