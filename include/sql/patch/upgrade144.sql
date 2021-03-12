begin;


CREATE OR REPLACE FUNCTION comptaproc.jrn_check_periode()
 RETURNS trigger
AS $function$
declare
bClosed bool;
str_status text;
ljr_tech_per jrn.jr_tech_per%TYPE;
ljr_def_id jrn.jr_def_id%TYPE;
lreturn jrn%ROWTYPE;
begin
if TG_OP='UPDATE' then
    ljr_tech_per :=OLD.jr_tech_per ;
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=NEW;
    if NEW.jr_date = OLD.jr_date then
        return NEW;
    end if;
    if comptaproc.is_closed(NEW.jr_tech_per,NEW.jr_def_id) = true then
              raise exception 'Periode fermee';
    end if;
end if;

if TG_OP='INSERT' then
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_tech_per :=NEW.jr_tech_per ;
    ljr_def_id :=NEW.jr_def_id;
    lreturn :=NEW;
end if;

if TG_OP='DELETE' then
    ljr_tech_per :=OLD.jr_tech_per;
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=OLD;
end if;

if comptaproc.is_closed (ljr_tech_per,ljr_def_id) = true then
       raise exception 'Periode fermee';
end if;

return lreturn;
end;
$function$
LANGUAGE plpgsql;


-- New right for action : delete
ALTER TABLE public.user_sec_action_profile drop CONSTRAINT user_sec_action_profile_ua_right_check;
ALTER TABLE public.user_sec_action_profile ADD CONSTRAINT user_sec_action_profile_ua_right_check check (ua_right in ('R','W','X','O'));

-- extension CRM : definition des options qu'on peut ajouter
-- public.document_option_ref definition

-- Drop table

-- DROP TABLE public.document_option_ref;


CREATE TABLE public.contact_option_ref (
	cor_id bigserial NOT NULL,
	cor_label varchar NOT NULL, -- Label de l'option
	cor_type int4 NOT NULL DEFAULT 0, -- 0 text , 1 select ,2 nombre , 3 date
	cor_value_select varchar NULL, -- Select values
	CONSTRAINT contact_option_ref_pk PRIMARY KEY (cor_id)
);
COMMENT ON TABLE public.contact_option_ref IS 'Option for the contact';

-- Column comments

COMMENT ON COLUMN public.contact_option_ref.cor_label IS 'Label de l''option';
COMMENT ON COLUMN public.contact_option_ref.cor_type IS '0 text , 1 select ,2 nombre , 3 date';
COMMENT ON COLUMN public.contact_option_ref.cor_value_select IS 'Select values';




-- renomme le menu
update menu_ref set me_description = 'Configuration des documents dans le suivi' ,me_file='cfg_action.inc.php' ,
me_description_etendue ='Vous permet d''ajouter de nouveaux type de documents pour le suivi (bordereau de livraison, devis..)',me_code='CFGACTION',
me_menu='Document Suivi'
where me_code='CFGCATDOC';


-- ajoute un menu pour les options de contacts
INSERT INTO public.menu_ref (me_code,me_menu,me_file,me_url,me_description,me_parameter,me_javascript,me_type,me_description_etendue) VALUES 
('CFGCONTACT','Contact','contact_option_ref.inc.php',NULL,'Configure les options pours les contacts multiples',NULL,NULL,'ME',NULL)
;

INSERT INTO public.profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep) VALUES 
('CFGCONTACT','DIVPARM',1,85,'E',0,56)
;



CREATE TABLE public.tag_group (
	tg_id bigserial NOT NULL,
	tg_name varchar NOT NULL
);
COMMENT ON TABLE public.tag_group IS 'Group of tags';

-- Column comments

COMMENT ON COLUMN public.tag_group.tg_name IS 'Nom du groupe';
ALTER TABLE public.tag_group ADD CONSTRAINT tag_group_pk PRIMARY KEY (tg_id);

-- public.jnt_tag_group_tag definition

-- Drop table

-- DROP TABLE public.jnt_tag_group_tag;

CREATE TABLE public.jnt_tag_group_tag (
	tag_group_id int8 NOT NULL,
	tag_id int8 NOT NULL,
	jt_id serial NOT NULL,
	CONSTRAINT jnt_tag_group_tag_pkey PRIMARY KEY (jt_id),
	CONSTRAINT jnt_tag_group_tag_un UNIQUE (tag_id, tag_group_id)
);
COMMENT ON TABLE public.jnt_tag_group_tag IS 'Many to Many table betwwen tag and tag group';


-- public.jnt_tag_group_tag foreign keys

ALTER TABLE public.jnt_tag_group_tag ADD CONSTRAINT jnt_tag_group_tag_fk FOREIGN KEY (tag_id) REFERENCES tags(t_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE public.jnt_tag_group_tag ADD CONSTRAINT jnt_tag_group_tag_fk_1 FOREIGN KEY (tag_group_id) REFERENCES tag_group(tg_id) ON UPDATE CASCADE ON DELETE CASCADE;


insert into action values (1025,'Ajout d''étiquette','followup','TAGADD');

-- public.action_person_option definition

-- Drop table

-- DROP TABLE public.action_person_option;

CREATE TABLE public.action_person_option (
	ap_id bigserial NOT NULL,
	ap_value varchar NULL, -- Value of the option
	contact_option_ref_id int8 NOT NULL, -- FK to contact_option
	action_person_id int8 NOT NULL, -- FK to action_person
	CONSTRAINT action_person_option_pk PRIMARY KEY (ap_id)
);
COMMENT ON TABLE public.action_person_option IS 'option for each contact';

-- Column comments

COMMENT ON COLUMN public.action_person_option.ap_value IS 'Value of the option';
COMMENT ON COLUMN public.action_person_option.contact_option_ref_id IS 'FK to contact_option';
COMMENT ON COLUMN public.action_person_option.action_person_id IS 'FK to action_person';


-- public.action_person_option foreign keys

ALTER TABLE public.action_person_option ADD CONSTRAINT action_person_option_fk FOREIGN KEY (action_person_id) REFERENCES action_person(ap_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE public.action_person_option ADD CONSTRAINT contact_option_ref_fk FOREIGN KEY (contact_option_ref_id) REFERENCES contact_option_ref(cor_id) ON UPDATE CASCADE ON DELETE CASCADE;

-- public.document_option definition

-- Drop table

-- DROP TABLE public.document_option;

CREATE TABLE public.document_option (
	do_id bigserial NOT NULL,
	do_code varchar(20) NOT NULL, -- Code of the option to add
	document_type_id int8 NULL, -- FK to document_type
	do_enable int4 NOT NULL DEFAULT 1, -- 1 the option is activated, 0 is inativated
	CONSTRAINT document_option_ref_pk PRIMARY KEY (do_id),
	CONSTRAINT document_option_un UNIQUE (do_code, document_type_id)
);
COMMENT ON TABLE public.document_option IS 'Reference of option addable to document_type';

-- Column comments

COMMENT ON COLUMN public.document_option.do_code IS 'Code of the option to add';
COMMENT ON COLUMN public.document_option.document_type_id IS 'FK to document_type';
COMMENT ON COLUMN public.document_option.do_enable IS '1 the option is activated, 0 is inativated';


-- public.document_option foreign keys

ALTER TABLE public.document_option ADD CONSTRAINT document_option_ref_fk FOREIGN KEY (document_type_id) REFERENCES document_type(dt_id) ON UPDATE CASCADE ON DELETE CASCADE;

insert into version (val,v_description) values (145,'Improve tags , add multiple contacts with options');
commit;
