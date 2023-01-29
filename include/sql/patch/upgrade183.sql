begin;
COMMENT ON COLUMN public.action_gestion.f_id_dest IS 'third party';
COMMENT ON COLUMN public.action_gestion.ag_priority IS 'Low, medium, important';
COMMENT ON COLUMN public.action_gestion.ag_dest IS 'is the profile which has to take care of this action';
COMMENT ON COLUMN public.action_gestion.ag_owner IS 'is the owner of this action';
COMMENT ON COLUMN public.action_gestion.ag_contact IS 'contact of the third part';
COMMENT ON COLUMN public.action_gestion.ag_title IS 'title';
COMMENT ON COLUMN public.action_gestion.ag_timestamp IS '';
COMMENT ON COLUMN public.action_gestion.ag_ref IS 'its reference';
COMMENT ON COLUMN public.action_gestion.ag_state IS 'state of the action same as document_state';

insert into version (val,v_description) values (184,'document table action_gestion');
commit;
