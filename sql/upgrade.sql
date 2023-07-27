begin;

COMMENT ON TABLE public.jrn_tax IS 'Additional Tax (Ploynesie)';
INSERT INTO public."parameter" (pr_id, pr_value)VALUES('MY_REPORT', 'N');
insert into version (val,v_description) values (184,'Mantis #2128: Clôture comptabilité française');
commit;

