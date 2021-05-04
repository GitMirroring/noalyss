begin;
CREATE OR REPLACE FUNCTION comptaproc.account_update(p_f_id integer, p_account account_type)
 RETURNS integer
LANGUAGE plpgsql
AS $function$
declare
	nMax fiche.f_id%type;
	nCount integer;
	nParent tmp_pcmn.pcm_val_parent%type;
	sName varchar;
	first text;
	second text;
begin

	if length(trim(p_account)) != 0 then
		-- 2 accounts in card separated by comma
		if position (',' in p_account) = 0 then
			p_account := format_account(p_account);
			select count(*) into nCount from tmp_pcmn where pcm_val=p_account;
			if nCount = 0 then
				select ad_value into sName from
					fiche_detail
					where
					ad_id=1 and f_id=p_f_id;
					nParent:=account_parent(p_account);
					raise notice 'insert into tmp_pcmn % %',p_account,sName;
					insert into tmp_pcmn(pcm_val,pcm_lib,pcm_val_parent) values (p_account,sName,nParent);
			end if;
		else
			raise info 'presence of a comma';
			-- there is 2 accounts separated by a comma
			first := split_part(p_account,',',1);
			second := split_part(p_account,',',2);
			-- check there is no other coma
			raise info 'first value % second value %', first, second;
	
			if  position (',' in first) != 0 or position (',' in second) != 0 then
				raise exception 'Too many comas, invalid account';
			end if;
			-- check that both account are in PCMN

		end if;
	else
		-- account is null
		update fiche_detail set ad_value=null where f_id=p_f_id and ad_id=5 ;
		return 0;
	end if;

	update fiche_detail set ad_value=p_account where f_id=p_f_id and ad_id=5 ;

return 0;
end;
$function$;


CREATE OR REPLACE FUNCTION comptaproc.account_insert(p_f_id integer, p_account text)
 RETURNS text
 LANGUAGE plpgsql
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

	if p_account is not null and length(trim(p_account)) != 0 then
	-- if there is coma in p_account, treat normally
		if position (',' in p_account) = 0 then
			raise info 'p_account is not empty';
				s_account := format_account(substr( p_account,1 , 40)::account_type);
				select count(*)  into nCount from tmp_pcmn where pcm_val=s_account::account_type;
				raise notice 'found in tmp_pcm %',nCount;
				if nCount !=0  then
					raise info 'this account exists in tmp_pcmn ';
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
		else
		raise info 'presence of a comma';
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
	else
	raise info 'A000 : p_account is  empty';
		select fd_id into nFd_id from fiche where f_id=p_f_id;
		bAuto:= account_auto(nFd_id);

		select fd_class_base into sClass_base from fiche_def where fd_id=nFd_id;
raise info 'sClass_Base : %',sClass_base;
		if bAuto = true and sClass_base similar to '[[:digit:]]*'  then
			raise info 'account generated automatically';
			sNew:=account_compute(p_f_id);
			raise info 'sNew %', sNew;
			select ad_value into sName from
				fiche_detail
			where
				ad_id=1 and f_id=p_f_id;
			nParent:=account_parent(sNew);
			sNew := account_add  (sNew,sName);
			perform attribut_insert(p_f_id,5,sNew);

		else
		-- if there is an account_base then it is the default
		      select fd_class_base::account_type into sNew from fiche_def join fiche using (fd_id) where f_id=p_f_id;
			if sNew is null or length(trim(sNew)) = 0 then
				raise notice 'count is null';
				 perform attribut_insert(p_f_id,5,null);
			else
				 perform attribut_insert(p_f_id,5,sNew);
			end if;
		end if;
	end if;

return 0;
end;
$function$;


insert into version (val,v_description) values (159,'correct bug when inserting accounting');
commit ;