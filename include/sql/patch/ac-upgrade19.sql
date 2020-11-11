begin;

ALTER TABLE public.audit_connect DROP CONSTRAINT valid_state ;
ALTER TABLE public.audit_connect ADD CONSTRAINT valid_state CHECK ( ac_state in  ('FAIL','SUCCESS','AUDIT','ADMIN'));

select upgrade_repo(19);
commit;