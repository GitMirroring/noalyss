alter table action_gestion drop ag_ref_ag_id;
--- repository insert into theme (the_name,the_filestyle) values ('Classic 692','style-r692.css');

ALTER TABLE tmp_pcmn   ADD CONSTRAINT id_ux UNIQUE(id);
ALTER TABLE tmp_pcmn ADD COLUMN id bigint;
ALTER TABLE tmp_pcmn ALTER COLUMN id SET NOT NULL;
ALTER TABLE tmp_pcmn ALTER COLUMN id SET DEFAULT nextval('tmp_pcmn_id_seq'::regclass);
COMMENT ON COLUMN tmp_pcmn.id IS 'allow to identify the row, it is unique and not null (pseudo pk)';




