begin;
ALTER TABLE public.parm_periode ADD p_exercice_label text NULL;
COMMENT ON COLUMN public.parm_periode.p_exercice_label IS 'label of the exercice';
update parm_periode set p_exercice_label =p_exercice ;
ALTER TABLE public.parm_periode ALTER COLUMN p_exercice_label SET NOT NULL;

COMMENT ON COLUMN public.parm_periode.p_start IS 'Start date of periode';
COMMENT ON COLUMN public.parm_periode.p_end IS 'End date of periode';
COMMENT ON COLUMN public.parm_periode.p_exercice IS 'Exercice';
COMMENT ON COLUMN public.parm_periode.p_closed IS 'is closed';
COMMENT ON COLUMN public.parm_periode.p_central IS 'is centralized (obsolete)';


CREATE OR REPLACE FUNCTION comptaproc.check_periode()
 RETURNS trigger
 AS $function$
declare 
  nPeriode int;
  nExerciceLabel int;
begin
	nPeriode:=periode_exist(to_char(NEW.p_start,'DD.MM.YYYY'),NEW.p_id);
-- check that the new - updated period doesn't overlap anything
	if nPeriode <> -1 then
       raise info 'Overlap periode start % periode %',NEW.p_start,nPeriode;
		return null;
	end if;
	if new.p_exercice_label is null or trim (new.p_exercice_label ) = '' then
		new.p_exercice_label := new.p_exercice;
	end if;
	select count(*) into nExerciceLabel 
		from parm_periode 
		where 
		(p_exercice =new.p_exercice and p_exercice_label <> new.p_exercice_label) 
		or 
		(p_exercice <> new.p_exercice and p_exercice_label = new.p_exercice_label);
		
	if nExerciceLabel > 0 then
		raise exception 'a label cannot be on two exercices';
		return null;
	end if;

return NEW;
end;
$function$
LANGUAGE plpgsql;


insert into version (val,v_description) values (172,'Add free label for exercice');
commit;