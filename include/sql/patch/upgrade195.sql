begin;


DROP TRIGGER t_jrnx_upd ON public.jrnx;


insert into version (val,v_description) values (196,'remove trigger update on JRNX ');
commit;