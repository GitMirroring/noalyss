begin;

insert into action (ac_id,ac_description,ac_module,ac_code) values (1130,'Modifier le numéro de pièce','compta','UPDRECEIPT') on conflict do nothing;
insert into action (ac_id,ac_description,ac_module,ac_code) values (1140,'Modifier la date d''une operation' ,'compta','UPDDATE') on conflict do nothing;

insert into user_sec_act(ua_login,ua_act_id) select distinct ua_login,1130 from user_sec_act;
insert into user_sec_act(ua_login,ua_act_id) select distinct ua_login,1140 from user_sec_act;


insert into version (val,v_description) values (194,'Renforcement sécurité');
commit;