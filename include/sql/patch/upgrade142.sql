begin;

CREATE OR REPLACE FUNCTION comptaproc.find_pcm_type(pp_value account_type)
 RETURNS text
AS $BODY$
declare
	str_type parm_poste.p_type%TYPE;
	str_value parm_poste.p_type%TYPE;
	nLength integer;
begin
	str_value:=pp_value;
	nLength:=length(str_value::text);

	while nLength > 0 loop
		select p_type into str_type from parm_poste where p_value=str_value;
		if FOUND then
			raise info 'Type of %s is %s',str_value,str_type;
			return str_type;
		end if;
		nLength:=nLength-1;
		str_value:=substring(str_value::text from 1 for nLength)::account_type;
	end loop;
-- Si non trouvé dans PARM_POSTE, prend le type du premier parent trouvé
--
	str_value := pp_value;
	nLength:=length(str_value::text);
	str_value:=substring(str_value::text from 1 for nLength)::account_type;
	while nLength > 0 loop
		select pcm_type into str_type from tmp_pcmn tp where pcm_val=str_value;
		if FOUND then
			raise info 'Type of %s is %s',str_value,str_type;
			return str_type;
		end if;
		nLength:=nLength-1;
		str_value:=substring(str_value::text from 1 for nLength)::account_type;
	end loop;
-- si ni parent ou parm_poste alors return CON
return 'CON';
end;
$BODY$
LANGUAGE plpgsql;

insert into version (val,v_description) values (143,'Corrige function find_pcm_type');
commit ;
