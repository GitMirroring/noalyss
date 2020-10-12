begin;

ALTER TABLE public.action_person drop CONSTRAINT action_gestion_ag_id_fk2 ;
ALTER TABLE public.action_person ADD CONSTRAINT action_gestion_ag_id_fk2 FOREIGN KEY (ag_id) REFERENCES action_gestion(ag_id) on delete cascade on update cascade;

insert into version (val,v_description) values (147,'Cascade delete on action_gestion');
commit;
