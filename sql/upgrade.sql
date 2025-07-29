-- change color selected menu
update parm_appearance set a_value='#000074' where a_code='MENU1-SELECTED' and a_value='#7191ea';
update parm_appearance set a_value='#FAFAFA' where a_code='BODY' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#FAFAFA' where a_code='FOLDER' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#506cb8 ' where a_code='MENU1-SELECTED' and lower(a_value)='#000074 ';

ALTER TABLE public.todo_list ALTER COLUMN tl_date drop NOT NULL;


update menu_ref set me_javascript='bookmark.show(<DOSSIER>)' where me_code='BOOKMARK';

alter table tool_uos add created_date timestamp default now();
update "parameter" set pr_id="MY_PHONE" where pr_id="MY_TEL";
update "parameter" set pr_id="MY_CP" where pr_id="MY_POSTCODE";
update "parameter" set pr_id="MY_COMMUNE" where pr_id="MY_CITY";
update "parameter" set pr_id="MY_PAYS" where pr_id="MY_COUNTRY";

insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(55,'SIREN','text',20,1,14);
insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(56,'SIRET','text',20,1,15);