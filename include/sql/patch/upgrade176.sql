begin;

ALTER TABLE public.jrn_def ADD jrn_def_quantity int2 NULL DEFAULT 1;
COMMENT ON COLUMN public.jrn_def.jrn_def_quantity IS 'Use the quantity column, 0->disable,1->enable,used only with Sale and Purchase otherwise ignored';
update public.jrn_def set jrn_def_quantity=1;
ALTER TABLE public.jrn_def ALTER COLUMN jrn_def_quantity SET NOT NULL;
-- change PARAM by CFG

update menu_ref set me_code='CFG' , me_menu='Configuration' ,me_description ='Configuration de votre dossier' where me_code='PARAM';

update profile_menu set me_code ='CFG' where me_code='PARAM';
update profile_menu set me_code_dep  ='CFG' where me_code_dep ='PARAM';
insert into version (val,v_description) values (177,'Use of quantity column optional and rename PARAM');
commit;