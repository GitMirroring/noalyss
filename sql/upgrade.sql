
CREATE OR REPLACE FUNCTION comptaproc.jrn_check_periode()
 RETURNS trigger
 LANGUAGE plpgsql
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
$function$;
LANGUAGE plpgsql;

-- New right for action : delete
ALTER TABLE public.user_sec_action_profile drop CONSTRAINT user_sec_action_profile_ua_right_check;
ALTER TABLE public.user_sec_action_profile ADD CONSTRAINT user_sec_action_profile_ua_right_check check (ua_right in ('R','W','X','O'));

-- extension CRM : definition des options qu'on peut ajouter
-- public.document_option_ref definition

-- Drop table

-- DROP TABLE public.document_option_ref;

CREATE TABLE public.document_option_ref (
	do_id bigserial NOT NULL,
	do_code varchar(20) NOT NULL, -- Code of the option to add
	document_type_id int8 NULL, -- FK to document_type
	CONSTRAINT document_option_ref_pk PRIMARY KEY (do_id)
);
COMMENT ON TABLE public.document_option_ref IS 'Reference of option of document_type';

-- Column comments

COMMENT ON COLUMN public.document_option_ref.do_code IS 'Code of the option to add';
COMMENT ON COLUMN public.document_option_ref.document_type_id IS 'FK to document_type';


-- public.document_option_ref foreign keys

ALTER TABLE public.document_option_ref ADD CONSTRAINT document_option_ref_fk FOREIGN KEY (document_type_id) REFERENCES document_type(dt_id) ON UPDATE CASCADE ON DELETE CASCADE;


-- Drop table

CREATE TABLE public.contact_option_ref (
	cor_id bigserial NOT NULL,
	cor_label varchar NOT NULL, -- Label de l'option
	cor_type int4 NOT NULL DEFAULT 0, -- 0 text , 1 select ,2 nombre , 3 date
	cor_value_json json NULL, -- json object if cor_type is a select
	document_option_id int8 NOT NULL, -- FK to document_option
	CONSTRAINT contact_option_ref_pk PRIMARY KEY (cor_id),
	CONSTRAINT contact_option_ref_fk FOREIGN KEY (document_option_id) REFERENCES document_option(do_id) ON UPDATE CASCADE ON DELETE CASCADE
);
COMMENT ON TABLE public.contact_option_ref IS 'Option for the contact';

-- Column comments

COMMENT ON COLUMN public.contact_option_ref.cor_label IS 'Label de l''option';
COMMENT ON COLUMN public.contact_option_ref.cor_type IS '0 text , 1 select ,2 nombre , 3 date';
COMMENT ON COLUMN public.contact_option_ref.cor_value_json IS 'json object if cor_type is a select';
COMMENT ON COLUMN public.contact_option_ref.document_option_id IS 'FK to document_option';


CREATE TABLE public.action_person_option (
	ap_id bigserial NOT NULL,
	ap_value varchar NULL, -- Value of the option
	contact_option_ref_id int8 NOT NULL,
	action_person_id int8 NOT NULL,
	CONSTRAINT action_person_option_pk PRIMARY KEY (ap_id),
	CONSTRAINT action_person_option_fk FOREIGN KEY (action_person_id) REFERENCES action_person(ap_id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT contact_option_ref_fk FOREIGN KEY (contact_option_ref_id) REFERENCES contact_option_ref(cor_id) ON UPDATE CASCADE ON DELETE CASCADE
);
COMMENT ON TABLE public.action_person_option IS 'option for each contact';

-- Column comments

COMMENT ON COLUMN public.action_person_option.ap_value IS 'Value of the option';

-- renomme le menu
update menu_ref set me_description = 'Configuration des documents dans le suivi' ,me_file='cfg_action.inc.php' ,
me_description_etendue ='Vous permet d''ajouter de nouveaux type de documents pour le suivi (bordereau de livraison, devis..)',me_code='CFGACTION',
me_menu='Document Suivi'
where me_code='CFGCATDOC';


ALTER TABLE public.document_option ADD do_activate int NOT NULL DEFAULT 1;
COMMENT ON COLUMN public.document_option.do_activate IS '1 the option is activated, 0 is inativated';

ALTER TABLE public.document_option ADD CONSTRAINT document_option_un UNIQUE (do_code,document_type_id);
ALTER TABLE public.document_option RENAME COLUMN do_activate TO do_enable;

-- ajoute un menu pour les options de contacts
INSERT INTO public.menu_ref (me_code,me_menu,me_file,me_url,me_description,me_parameter,me_javascript,me_type,me_description_etendue) VALUES 
('CFGCONTACT','Contact','contact_option_ref.inc.php',NULL,'Configure les options pours les contacts multiples',NULL,NULL,'ME',NULL)
;

INSERT INTO public.profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep) VALUES 
('CFGCONTACT','DIVPARM',1,85,'E',0,56)
;

-- contact option globale pour toutes les actions
ALTER TABLE public.contact_option_ref DROP COLUMN document_option_id;

-- on utilise un varchar pour stocker les possibilités
ALTER TABLE public.contact_option_ref DROP COLUMN cor_value_json;
ALTER TABLE public.contact_option_ref ADD cor_value_select varchar NULL;
COMMENT ON COLUMN public.contact_option_ref.cor_value_select IS 'Select values';