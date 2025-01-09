-- change color selected menu
update parm_appearance set a_value='#000074' where a_code='MENU1-SELECTED' and a_value='#7191ea';
update parm_appearance set a_value='#FAFAFA' where a_code='BODY' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#FAFAFA' where a_code='FOLDER' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#506cb8 ' where a_code='MENU1-SELECTED' and lower(a_value)='#000074 ';

ALTER TABLE public.todo_list ALTER COLUMN tl_date drop NOT NULL;
