begin;
insert into parm_appearance values ('INNER-BOX-TITLE' , '#023575');
insert into menu_ref(me_code,me_menu,me_file,me_description,me_type,me_description_etendue) values ('NCOL','Apparence','noalyss-color.inc.php','Couleur de NOALYSS','ME','Personnalisation des couleurs de NOYALYSS');
INSERT INTO PROFILE_MENU(ME_CODE, ME_CODE_DEP, P_ID, P_ORDER, P_TYPE_DISPLAY, PM_DEFAULT, PM_ID_DEP) values ('NCOL','PARAM',1,5,'E',0,45);

insert into version (val,v_description) values (176,'Folder Appearance : dialog box');
commit;
