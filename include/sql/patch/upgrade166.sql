begin;


CREATE OR REPLACE FUNCTION comptaproc.attribut_insert(p_f_id integer, p_ad_id integer, p_value character varying)
 RETURNS void
 AS $function$
declare 
	nResult bigint;
begin
	update fiche_detail set ad_value=p_value where ad_id=p_ad_id and f_id=p_f_id returning jft_id into nResult;
	if nResult is null then
		insert into fiche_detail (f_id,ad_id, ad_value) values (p_f_id,p_ad_id,p_value);
	end if;

return;
end;
$function$
LANGUAGE plpgsql;
 
CREATE OR REPLACE FUNCTION comptaproc.account_insert(p_f_id integer, p_account text)
 RETURNS text
AS $function$
declare
	nParent tmp_pcmn.pcm_val_parent%type;
	sName varchar;
	sNew tmp_pcmn.pcm_val%type;
	bAuto bool;
	nFd_id integer;
	sClass_Base fiche_def.fd_class_base%TYPE;
	nCount integer;
	first text;
	second text;
	s_account text;
begin
	raise info 'ai17 : param card % account %',p_f_id,p_account;
	-- accouting is given
	if p_account is not null and length(trim(p_account)) != 0 then
	-- if there is coma in p_account, treat normally
		if position (',' in p_account) = 0 then
			raise info 'ai20.p_account is not empty';
				s_account := format_account(substr( p_account,1 , 40)::account_type);
				select count(*)  into nCount from tmp_pcmn where pcm_val=s_account::account_type;
				raise notice 'ai24.found in tmp_pcm %',nCount;
				if nCount !=0  then
					raise info 'ai25.this account exists in tmp_pcmn ';
					perform attribut_insert(p_f_id,5,s_account);
				   else
				       -- account doesn't exist, create it
					select ad_value into sName from
						fiche_detail
					where
					ad_id=1 and f_id=p_f_id;

					nParent:=account_parent(s_account::account_type);
					insert into tmp_pcmn(pcm_val,pcm_lib,pcm_val_parent) values (s_account::account_type,sName,nParent);
					perform attribut_insert(p_f_id,5,s_account);
					
				end if;
				return s_account;
		else
			raise info 'ai42.presence of a comma';
			-- there is 2 accounts separated by a comma
			first := split_part(p_account,',',1);
			second := split_part(p_account,',',2);
			-- check there is no other coma
			raise info 'first value % second value %', first, second;

		if  position (',' in first) != 0 or position (',' in second) != 0 then
			raise exception 'Too many comas, invalid account';
		end if;
		perform attribut_insert(p_f_id,5,p_account);
		
		end if;
	raise info 'ai55 : end ';
	return s_account;
	end if;

-- accouting is empty and must be computed
---------------------------------------------------
	raise info 'ai61 : p_account is  empty';
		select fd_id into nFd_id from fiche where f_id=p_f_id;
		bAuto:= account_auto(nFd_id);

		select fd_class_base into sClass_base from fiche_def where fd_id=nFd_id;
raise info 'ai67.sClass_Base : %',sClass_base;
		if bAuto = true and sClass_base similar to '[[:digit:]]*'  then
			raise info 'ai66.account generated automatically';
			sNew:=account_compute(p_f_id);
			raise info 'ai68.sNew %', sNew;
			select ad_value into sName from
				fiche_detail
			where
				ad_id=1 and f_id=p_f_id;
			nParent:=account_parent(sNew);
			sNew := account_add  (sNew,sName);
			raise info 'ai78.sNew % name %s', sNew,sName;
			perform attribut_insert(p_f_id,5,sNew);
			return sNew;
		else
		-- if there is an account_base then it is the default
		    raise info 'ai82.account NOT generated automatically and class_base is [%]',sClass_base;
			if trim(coalesce(sClass_base::text,'')) = '' then
				raise notice 'ai81.count is null';
				 perform attribut_insert(p_f_id,5,null);
			else
				raise notice 'ai87.Class base in the Accounting';
				 perform attribut_insert(p_f_id,5,sClass_base);
			end if;
			return sClass_base;
		end if;

raise notice 'ai89.account_insert nothing done : error';

end;
$function$
LANGUAGE plpgsql;

insert into version (val,v_description) values (167,'Card accounting created automatically');
commit ;

