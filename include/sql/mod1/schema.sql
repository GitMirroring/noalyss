

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;


CREATE SCHEMA comptaproc;



CREATE SCHEMA tva_belge;



CREATE DOMAIN public.account_type AS character varying(40);



CREATE TYPE public.anc_table_account_type AS (
	po_id bigint,
	pa_id bigint,
	po_name text,
	po_description text,
	sum_amount numeric(25,4),
	card_account text,
	name text
);



CREATE TYPE public.anc_table_card_type AS (
	po_id bigint,
	pa_id bigint,
	po_name text,
	po_description text,
	sum_amount numeric(25,4),
	f_id bigint,
	card_account text,
	name text
);



CREATE TYPE public.menu_tree AS (
	code text,
	description text
);



CREATE FUNCTION comptaproc.account_add(p_id public.account_type, p_name character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
declare
    nParent tmp_pcmn.pcm_val_parent%type;
    nCount integer;
    sReturn text;
begin
    -- patch 189
    sReturn:= format_account(p_id);
    select count(*) into nCount from tmp_pcmn where pcm_val=sReturn;
    if nCount = 0 then
        nParent=account_parent(p_id);
        insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent)
        values (p_id, p_name,nParent) returning pcm_val into sReturn;
    end if;
    return sReturn;
end ;
$$;



CREATE FUNCTION comptaproc.account_alphanum() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	l_auto bool;
begin
	l_auto := true;
	select pr_value into l_auto from parameter where pr_id='MY_ALPHANUM';
	if l_auto = 'N' or l_auto is null then
		l_auto:=false;
	end if;
	return l_auto;
end;
$$;



CREATE FUNCTION comptaproc.account_auto(p_fd_id integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	l_auto bool;
begin

	select fd_create_account into l_auto from fiche_def where fd_id=p_fd_id;
	if l_auto is null then
		l_auto:=false;
	end if;
	return l_auto;
end;
$$;



CREATE FUNCTION comptaproc.account_compute(p_f_id integer) RETURNS public.account_type
    LANGUAGE plpgsql
    AS $$
declare
    class_base fiche_def.fd_class_base%type;
    maxcode numeric;
    sResult account_type;
    bAlphanum bool;
    sName text;
    nCount integer;
    sNumber text;
begin
    -- patch 189
    select fd_class_base into class_base
    from
        fiche_def join fiche using (fd_id)
    where
            f_id=p_f_id;

    bAlphanum := account_alphanum();
    if bAlphanum = false  then
        select max (pcm_val::numeric) into maxcode
        from tmp_pcmn
        where pcm_val_parent = class_base and pcm_val !~* '[[:alpha:]]'  ;
        if maxcode is null	or length(maxcode::text) < length(class_base)+4 then
            maxcode:=class_base::numeric*10000+1;
        else
            select max (pcm_val::numeric) into maxcode
            from tmp_pcmn
            where pcm_val !~* '[[:alpha:]]'
              and pcm_val_parent = class_base
              and substr(pcm_val::text,1,length(class_base))=class_base;

            sNumber := substr(maxcode::text,length(class_base)+1);
            nCount := sNumber::numeric+1;
            sNumber := lpad (nCount::text,4,'0');

            maxcode:=class_base||sNumber;
        end if;
        sResult:=maxcode::account_type;
    else
        -- if alphanum, use name
        select ad_value into sName from fiche_detail where f_id=p_f_id and ad_id=1;
        if sName is null then
            raise exception 'Cannot compute an accounting without the name of the card for %',p_f_id;
        end if;
        sResult := account_compute_alpha(class_base,sName);
    end if;
    return sResult;
end;
$$;



CREATE FUNCTION comptaproc.account_compute_alpha(p_class text, p_name text) RETURNS public.account_type
    LANGUAGE plpgsql
    AS $$
declare
    sResult account_type;
    sAccount account_type;
    sFormatedAccount account_type;
    nCount int;
    idx int :=0;
begin
    sFormatedAccount := comptaproc.format_account(p_name);

    sAccount := p_class||substring(sFormatedAccount for 5);
    nCount := 0;
    loop
        select count(*) into nCount from tmp_pcmn where pcm_val = comptaproc.format_account(sAccount);

        exit when nCount = 0;
        idx := idx + 1;
        sAccount := p_class || substring(sFormatedAccount for 5)||idx::text;
    end loop;
    sResult := comptaproc.format_account(sAccount);
    return sResult;
end;
$$;



CREATE FUNCTION comptaproc.account_insert(p_f_id integer, p_account text) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
    -- patch 189
    -- accouting is given
    if p_account is not null and length(trim(p_account)) != 0 then
        -- if there is coma in p_account, treat normally
        if position (',' in p_account) = 0 then
            s_account := format_account(substr( p_account,1 , 40)::account_type);
            select count(*)  into nCount from tmp_pcmn where pcm_val=s_account::account_type;
            if nCount !=0  then
                perform attribut_insert(p_f_id,5,s_account);
            else
                -- account doesn't exist, create it
                select ad_value into sName from
                    fiche_detail
                where
                        ad_id=1 and f_id=p_f_id;
                -- retrieve parent account from card
                select fd_class_base::account_type into nParent from fiche_def where fd_id=(select fd_id from fiche where f_id=p_f_id);
                if nParent = null or nParent = '' then
                    nParent:=account_parent(s_account::account_type);
                end if;
                insert into tmp_pcmn(pcm_val,pcm_lib,pcm_val_parent) values (s_account::account_type,sName,nParent);
                perform attribut_insert(p_f_id,5,s_account);

            end if;
            return s_account;
        else
            -- there is 2 accounts separated by a comma
            first := split_part(p_account,',',1);
            second := split_part(p_account,',',2);
            -- check there is no other coma

            if  position (',' in first) != 0 or position (',' in second) != 0 then
                raise exception 'Too many comas, invalid account';
            end if;
            perform attribut_insert(p_f_id,5,p_account);

        end if;
        return s_account;
    end if;

    select fd_id into nFd_id from fiche where f_id=p_f_id;
    bAuto:= account_auto(nFd_id);

    select fd_class_base into sClass_base from fiche_def where fd_id=nFd_id;
    if bAuto = true and sClass_base similar to '[[:digit:]]*'  then
        sNew:=account_compute(p_f_id);
        select ad_value into sName from
            fiche_detail
        where
                ad_id=1 and f_id=p_f_id;
        nParent:=sClass_Base::account_type;
        sNew := account_add  (sNew,sName);
        update tmp_pcmn set pcm_val_parent=nParent where pcm_val=sNew;
        perform attribut_insert(p_f_id,5,sNew);
        return sNew;
    else
        -- if there is an account_base then it is the default
        if trim(coalesce(sClass_base::text,'')) = '' then
            perform attribut_insert(p_f_id,5,null);
        else
            perform attribut_insert(p_f_id,5,sClass_base);
        end if;
        return sClass_base;
    end if;

    raise notice 'ai89.account_insert nothing done : error';

end;
$$;



CREATE FUNCTION comptaproc.account_parent(p_account public.account_type) RETURNS public.account_type
    LANGUAGE plpgsql
    AS $$
declare
	sSubParent tmp_pcmn.pcm_val_parent%type;
	sResult tmp_pcmn.pcm_val_parent%type;
	nCount integer;
begin
	if p_account is NULL then
		return NULL;
	end if;
	sSubParent:=p_account;
	while true loop
		select count(*) into nCount
		from tmp_pcmn
		where
		pcm_val = sSubParent;
		if nCount != 0 then
			sResult:= sSubParent;
			exit;
		end if;
		sSubParent:= substr(sSubParent,1,length(sSubParent)-1);
		if length(sSubParent) <= 0 then
			raise exception 'Impossible de trouver le compte parent pour %',p_account;
		end if;
		raise notice 'sSubParent % % ',sSubParent,length(sSubParent);
	end loop;
	raise notice 'account_parent : Parent is %',sSubParent;
	return sSubParent;
end;
$$;



CREATE FUNCTION comptaproc.account_update(p_f_id integer, p_account public.account_type) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;



CREATE FUNCTION comptaproc.action_gestion_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
NEW.ag_title := substr(trim(NEW.ag_title),1,70);
NEW.ag_hour := substr(trim(NEW.ag_hour),1,5);
NEW.ag_owner := lower(NEW.ag_owner);
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.action_gestion_related_ins_up() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
	nTmp bigint;
begin

if NEW.aga_least > NEW.aga_greatest then
	nTmp := NEW.aga_least;
	NEW.aga_least := NEW.aga_greatest;
	NEW.aga_greatest := nTmp;
end if;

if NEW.aga_least = NEW.aga_greatest then
	return NULL;
end if;

return NEW;

end;
$$;



CREATE FUNCTION comptaproc.anc_correct_tvand() RETURNS void
    LANGUAGE plpgsql
    AS $$ 
declare
        n_count numeric;
        i record;
        newrow_tva record;
begin
         for i in select * from operation_analytique where oa_jrnx_id_source is not null loop
         -- Get all the anc accounting from the base operation and insert the missing record for VAT 
                for newrow_tva in select *  from operation_analytique where j_id=i.oa_jrnx_id_source and po_id <> i.po_id loop
                    
                        -- check if the record is yet present
                        select count(*) into n_count from operation_analytique where  po_id=newrow_tva.po_id and oa_jrnx_id_source=i.oa_jrnx_id_source;

                        if n_count = 0 then
                          raise info 'insert operation analytique po_id = % oa_group = % ',i.po_id, i.oa_group;
                          insert into operation_analytique 
                          (po_id,oa_amount,oa_description,oa_debit,j_id,oa_group,oa_date,oa_jrnx_id_source,oa_positive)
                          values (newrow_tva.po_id,i.oa_amount,i.oa_description,i.oa_debit,i.j_id,i.oa_group,i.oa_date,i.oa_jrnx_id_source,i.oa_positive);
                        end if;
         
                end loop;

         
         end loop;
end;
 $$;



CREATE FUNCTION comptaproc.attribut_insert(p_f_id integer, p_ad_id integer, p_value character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
	nResult bigint;
begin
	update fiche_detail set ad_value=p_value where ad_id=p_ad_id and f_id=p_f_id returning jft_id into nResult;
	if nResult is null then
		insert into fiche_detail (f_id,ad_id, ad_value) values (p_f_id,p_ad_id,p_value);
	end if;

return;
end;
$$;



CREATE FUNCTION comptaproc.attribute_correct_order() RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
    crs_correct cursor for select A.jnt_id,A.jnt_order from jnt_fic_attr as A join jnt_fic_attr as B using (fd_id) where A.jnt_order=B.jnt_order and A.jnt_id > B.jnt_id;
    rec record;
begin
	open crs_correct;
	loop
	fetch crs_correct into rec;
	if NOT FOUND then
		close crs_correct;
		return;
	end if;
	update jnt_fic_attr set jnt_order=jnt_order + 1 where jnt_id = rec.jnt_id;
	end loop;
	close crs_correct;
	perform attribute_correct_order ();
end;
$$;



CREATE FUNCTION comptaproc.card_after_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

	delete from action_gestion where f_id_dest = OLD.f_id;
	return OLD;

end;
$$;



CREATE FUNCTION comptaproc.card_class_base(p_f_id integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
declare
	n_poste fiche_def.fd_class_base%type;
begin

	select fd_class_base into n_poste from fiche_def join fiche using
(fd_id)
	where f_id=p_f_id;
	if not FOUND then
		raise exception 'Invalid fiche card_class_base(%)',p_f_id;
	end if;
return n_poste;
end;
$$;



CREATE FUNCTION comptaproc.category_card_before_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin
    if OLD.fd_id > 499000 then
        return null;
    end if;
    return OLD;

end;
$$;



CREATE FUNCTION comptaproc.check_balance(p_grpt integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
declare
	amount_jrnx_debit numeric;
	amount_jrnx_credit numeric;
	amount_jrn numeric;
begin
	select coalesce(sum (j_montant),0) into amount_jrnx_credit
	from jrnx
		where
	j_grpt=p_grpt
	and j_debit=false;

	select coalesce(sum (j_montant),0) into amount_jrnx_debit
	from jrnx
		where
	j_grpt=p_grpt
	and j_debit=true;

	select coalesce(jr_montant,0) into amount_jrn
	from jrn
	where
	jr_grpt_id=p_grpt;

	if ( amount_jrnx_debit != amount_jrnx_credit )
		then
		return abs(amount_jrnx_debit-amount_jrnx_credit);
		end if;
	if ( amount_jrn != amount_jrnx_credit)
		then
		return -1*abs(amount_jrn - amount_jrnx_credit);
		end if;
	return 0;
end;
$$;



CREATE FUNCTION comptaproc.check_periode() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
  nPeriode int;
  nExerciceLabel int;
begin
	nPeriode:=periode_exist(to_char(NEW.p_start,'DD.MM.YYYY'),NEW.p_id);
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
$$;



CREATE FUNCTION comptaproc.correct_sequence(p_sequence text, p_col text, p_table text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
last_sequence int8;
max_sequence int8;
n integer;
begin
	select count(*) into n from pg_class where relkind='S' and relname=lower(p_sequence);
	if n = 0 then
		raise exception  ' Unknow sequence  % ',p_sequence;
	end if;
	select count(*) into n from pg_class where relkind='r' and relname=lower(p_table);
	if n = 0 then
		raise exception ' Unknow table  % ',p_table;
	end if;

	execute 'select last_value   from '||p_sequence into last_sequence;
	raise notice 'Last value of the sequence is %', last_sequence;

	execute 'select max('||p_col||')  from '||p_table into max_sequence;
	if  max_sequence is null then
		max_sequence := 0;
	end if;
	raise notice 'Max value of the sequence is %', max_sequence;
	max_sequence:= max_sequence +1;	
	execute 'alter sequence '||p_sequence||' restart with '||max_sequence;
return 0;

end;
$$;



COMMENT ON FUNCTION comptaproc.correct_sequence(p_sequence text, p_col text, p_table text) IS ' Often the primary key is a sequence number and sometimes the value of the sequence is not synchronized with the primary key ( p_sequence : sequence name, p_col : col of the pk,p_table : concerned table';



CREATE FUNCTION comptaproc.create_missing_sequence() RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
p_sequence text;
nSeq integer;
c1 cursor for select jrn_def_id from jrn_def;
begin
	open c1;
	loop
	   fetch c1 into nSeq;
	   if not FOUND THEN
	   	close c1;
	   	return 0;
	   end if;
	   p_sequence:='s_jrn_pj'||nSeq::text;
	execute 'create sequence '||p_sequence;
	end loop;
close c1;
return 0;

end;
$$;



CREATE FUNCTION comptaproc.drop_index(p_constraint character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
	nCount integer;
begin
	select count(*) into nCount from pg_indexes where indexname=p_constraint;
	if nCount = 1 then
	execute 'drop index '||p_constraint ;
	end if;
end;
$$;



CREATE FUNCTION comptaproc.drop_it(p_constraint character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
	nCount integer;
begin
	select count(*) into nCount from pg_constraint where conname=p_constraint;
	if nCount = 1 then
	execute 'alter table parm_periode drop constraint '||p_constraint ;
	end if;
end;
$$;



CREATE FUNCTION comptaproc.extension_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
 sCode text;
 sFile text;
begin
sCode:=trim(upper(NEW.ex_code));
sCode:=replace(sCode,' ','_');
sCode:=substr(sCode,1,15);
sCode=upper(sCode);
NEW.ex_code:=sCode;
sFile:=NEW.ex_file;
sFile:=replace(sFile,';','_');
sFile:=replace(sFile,'<','_');
sFile:=replace(sFile,'>','_');
sFile:=replace(sFile,'..','');
sFile:=replace(sFile,'&','');
sFile:=replace(sFile,'|','');



return NEW;

end;

$$;



CREATE FUNCTION comptaproc.fiche_account_parent(p_f_id integer) RETURNS public.account_type
    LANGUAGE plpgsql
    AS $$
declare
ret tmp_pcmn.pcm_val%TYPE;
begin
	select fd_class_base into ret from fiche_def join fiche using (fd_id) where f_id=p_f_id;
	if not FOUND then
		raise exception '% N''existe pas',p_f_id;
	end if;
	return ret;
end;
$$;



CREATE FUNCTION comptaproc.fiche_attribut_synchro(p_fd_id integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	-- this sql gives the f_id and the missing attribute (ad_id)
	list_missing cursor for select f_id,fd_id,ad_id,jnt_order from jnt_fic_attr join fiche as A using (fd_id) where fd_id=p_fd_id and ad_id not in (select ad_id from fiche join fiche_detail using (f_id) where fd_id=jnt_fic_attr.fd_id and A.f_id=f_id);
	rec record;
begin
	open list_missing;
	loop
	
	fetch list_missing into rec;
	IF NOT FOUND then
		exit;
	end if;
	
	-- now we insert into attr_value
	insert into fiche_detail (f_id,ad_id,ad_value) values (rec.f_id,rec.ad_id,null);
	end loop;
	close list_missing;
end; 
$$;



CREATE FUNCTION comptaproc.fiche_def_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin

if position (',' in NEW.fd_class_base) != 0 then
   NEW.fd_create_account='f';

end if;
return NEW;
end;$$;



CREATE FUNCTION comptaproc.fiche_detail_check() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	BEGIN
		if new.ad_id = 23 and coalesce (new.ad_value,'') = '' then 
			raise exception  'QUICKCODE can not be empty';
		end if;
	if new.ad_id = 1 and coalesce (new.ad_value,'') = '' then 
			raise exception  'NAME can not be empty';
		end if;
	return new;
	END;

$$;



CREATE FUNCTION comptaproc.fiche_detail_check_qcode() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
    i record;
begin
    if NEW.ad_id=23 and NEW.ad_value != OLD.ad_value then
        update jrnx set j_qcode=NEW.ad_value where j_qcode = OLD.ad_value;
        update op_predef_detail set opd_poste=NEW.ad_value where opd_poste=OLD.ad_value;
        for i in select ad_id from attr_def where ad_type = 'card' or ad_id=25 loop
                update fiche_detail set ad_value=NEW.ad_value where ad_value=OLD.ad_value and ad_id=i.ad_id;
                if i.ad_id=19 then
                    update stock_goods set sg_code=NEW.ad_value where sg_code=OLD.ad_value;
                end if;

            end loop;
    end if;
    return NEW;
end;
$$;



CREATE FUNCTION comptaproc.fill_quant_fin() RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
   sBank text;
   sCassa text;
   sCustomer text;
   sSupplier text;
   rec record;
   recBank record;
   recSupp_Cust record;
   nCount integer;
   nAmount numeric;
   nBank integer;
   nOther integer;
   nSupp_Cust integer;
begin
	select p_value into sBank from parm_code where p_code='BANQUE';
	select p_value into sCassa from parm_code where p_code='CAISSE';
	select p_value into sSupplier from parm_code where p_code='SUPPLIER';
	select p_value into sCustomer from parm_code where p_code='CUSTOMER';
	
	for rec in select jr_id,jr_grpt_id from jrn 
	    where jr_def_id in (select jrn_def_id from jrn_def where jrn_def_type='FIN')
		and jr_id not in (select jr_id from quant_fin)
	loop
		-- there are only 2 lines for bank operations
		-- first debit
		select count(j_id) into nCount from jrnx where j_grpt=rec.jr_grpt_id;
		if nCount > 2 then 
			raise notice 'Trop de valeur pour jr_grpt_id % count %',rec.jr_grpt_id,nCount;
			return;
		end if;
		nBank := 0; nOther:=0;
		for recBank in select  j_id, j_montant,j_debit,j_qcode,j_poste from jrnx where j_grpt=rec.jr_grpt_id
		loop
		if recBank.j_poste like sBank||'%' then
			-- retrieve f_id for bank
			select f_id into nBank from vw_poste_qcode where j_qcode=recBank.j_qcode;
			if recBank.j_debit = false then
				nAmount=recBank.j_montant*(-1);
			else 
				nAmount=recBank.j_montant;
			end if;
		else
			select f_id into nOther from vw_poste_qcode where j_qcode=recBank.j_qcode;
		end if;
		end loop;
		if nBank != 0 and nOther != 0 then
			insert into quant_fin (jr_id,qf_bank,qf_other,qf_amount) values (rec.jr_id,nBank,nOther,nAmount);
		end if;
	end loop;
	for rec in select jr_id,jr_grpt_id from jrn 
	    where jr_def_id in (select jrn_def_id from jrn_def where jrn_def_type='FIN') and jr_id not in (select jr_id from quant_fin)
	loop
		-- there are only 2 lines for bank operations
		-- first debit
		select count(j_id) into nCount from jrnx where j_grpt=rec.jr_grpt_id;
		if nCount > 2 then 
			raise notice 'Trop de valeur pour jr_grpt_id % count %',rec.jr_grpt_id,nCount;
			return;
		end if;
		nBank := 0; nOther:=0;
		for recBank in select  j_id, j_montant,j_debit,j_qcode,j_poste from jrnx where j_grpt=rec.jr_grpt_id
		loop
		if recBank.j_poste like sCassa||'%' then
			-- retrieve f_id for bank
			select f_id into nBank from vw_poste_qcode where j_qcode=recBank.j_qcode;
			if recBank.j_debit = false then
				nAmount=recBank.j_montant*(-1);
			else 
				nAmount=recBank.j_montant;
			end if;
		else
			select f_id into nOther from vw_poste_qcode where j_qcode=recBank.j_qcode;
		end if;
		end loop;
		if nBank != 0 and nOther != 0 then
			insert into quant_fin (jr_id,qf_bank,qf_other,qf_amount) values (rec.jr_id,nBank,nOther,nAmount);
		end if;
	end loop;

	for rec in select jr_id,jr_grpt_id from jrn 
	    where jr_def_id in (select jrn_def_id from jrn_def where jrn_def_type='FIN') and jr_id not in (select jr_id from quant_fin)
	loop
		-- there are only 2 lines for bank operations
		-- first debit
		select count(j_id) into nCount from jrnx where j_grpt=rec.jr_grpt_id;
		if nCount > 2 then 
			raise notice 'Trop de valeur pour jr_grpt_id % count %',rec.jr_grpt_id,nCount;
			return;
		end if;
		nSupp_Cust := 0; nOther:=0;
		for recSupp_Cust in select  j_id, j_montant,j_debit,j_qcode,j_poste from jrnx where j_grpt=rec.jr_grpt_id
		loop
		if recSupp_Cust.j_poste like sSupplier||'%'  then
			-- retrieve f_id for bank
			select f_id into nSupp_Cust from vw_poste_qcode where j_qcode=recSupp_Cust.j_qcode;
			if recSupp_Cust.j_debit = true then
				nAmount=recSupp_Cust.j_montant*(-1);
			else 
				nAmount=recSupp_Cust.j_montant;
			end if;
		else if  recSupp_Cust.j_poste like sCustomer||'%' then
			select f_id into nSupp_Cust from vw_poste_qcode where j_qcode=recSupp_Cust.j_qcode;
			if recSupp_Cust.j_debit = false then
				nAmount=recSupp_Cust.j_montant*(-1);
			else 
				nAmount=recSupp_Cust.j_montant;
			end if;
			else
			select f_id into nOther from vw_poste_qcode where j_qcode=recSupp_Cust.j_qcode;
			
			end if;
		end if;
		end loop;
		if nSupp_Cust != 0 and nOther != 0 then
			insert into quant_fin (jr_id,qf_bank,qf_other,qf_amount) values (rec.jr_id,nOther,nSupp_Cust,nAmount);
		end if;
	end loop;
	for rec in select jr_id,jr_grpt_id from jrn 
	    where jr_def_id in (select jrn_def_id from jrn_def where jrn_def_type='FIN') and jr_id not in (select jr_id from quant_fin)
	loop
		-- there are only 2 lines for bank operations
		-- first debit
		select count(j_id) into nCount from jrnx where j_grpt=rec.jr_grpt_id;
		if nCount > 2 then 
			raise notice 'Trop de valeur pour jr_grpt_id % count %',rec.jr_grpt_id,nCount;
			return;
		end if;
		nSupp_Cust := 0; nOther:=0;
		for recSupp_Cust in select  j_id, j_montant,j_debit,j_qcode,j_poste from jrnx where j_grpt=rec.jr_grpt_id
		loop
		if recSupp_Cust.j_poste like '441%'  then
			-- retrieve f_id for bank
			select f_id into nSupp_Cust from vw_poste_qcode where j_qcode=recSupp_Cust.j_qcode;
			if recSupp_Cust.j_debit = false then
				nAmount=recSupp_Cust.j_montant*(-1);
			else 
				nAmount=recSupp_Cust.j_montant;
			end if;
			else
			select f_id into nOther from vw_poste_qcode where j_qcode=recSupp_Cust.j_qcode;
			
			
		end if;
		end loop;
		if nSupp_Cust != 0 and nOther != 0 then
			insert into quant_fin (jr_id,qf_bank,qf_other,qf_amount) values (rec.jr_id,nOther,nSupp_Cust,nAmount);
		end if;
	end loop;
	return;
end;
$$;



CREATE FUNCTION comptaproc.find_pcm_type(pp_value public.account_type) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
return 'CON';
end;
$$;



CREATE FUNCTION comptaproc.find_periode(p_date text) RETURNS integer
    LANGUAGE plpgsql
    AS $$

declare n_p_id int4;
begin

select p_id into n_p_id
	from parm_periode
	where
		p_start <= to_date(p_date,'DD.MM.YYYY')
		and
		p_end >= to_date(p_date,'DD.MM.YYYY');

if NOT FOUND then
	return -1;
end if;

return n_p_id;

end;$$;



CREATE FUNCTION comptaproc.format_account(p_account public.account_type) RETURNS public.account_type
    LANGUAGE plpgsql
    AS $_$

declare

sResult account_type;

begin
sResult := lower(p_account);

sResult := translate(sResult,E'éèêëàâäïîüûùöôç','eeeeaaaiiuuuooc');
sResult := translate(sResult,E' $€µ£%.+-/\\!(){}(),;_&|"#''^<>*','');

return upper(sResult);

end;
$_$;



COMMENT ON FUNCTION comptaproc.format_account(p_account public.account_type) IS 'format the accounting :
- upper case
- remove space and special char.
';



CREATE FUNCTION comptaproc.format_quickcode(p_qcode text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
declare
    tText text;
BEGIN
    tText := lower(trim(p_qcode));
    tText := replace(tText,' ','');
    tText:= translate(tText,E' $€µ£%+/\\!(){}(),;&|"#''^<>*','');
    tText := translate(tText,E'éèêëàâäïîüûùöôç','eeeeaaaiiuuuooc');

    return upper(tText);
END;
$_$;



COMMENT ON FUNCTION comptaproc.format_quickcode(p_qcode text) IS 'Put in upper case and remove invalid char';



CREATE FUNCTION comptaproc.four_upper_letter() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    new.dc_code=transform_to_code(new.dc_code);
    new.dc_code:=substr(new.dc_code,1,4);
    return new;
END;
$$;



COMMENT ON FUNCTION comptaproc.four_upper_letter() IS 'Cut to the 4 first letter in uppercase';



CREATE FUNCTION comptaproc.get_follow_up_tree(action_gestion_id integer) RETURNS SETOF integer
    LANGUAGE plpgsql
    AS $$
declare
    i int;
    x int;
    e int;
begin
    for x in select aga_least
             from action_gestion_related
             where
                 aga_greatest = action_gestion_id
        loop
            return next x;

            for e in select *  from  comptaproc.get_follow_up_tree(x)
                loop
                    return next e;
                end loop;

        end loop;
    return;
end;
$$;



CREATE FUNCTION comptaproc.get_letter_jnt(a bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
declare
 nResult bigint;
begin
   select jl_id into nResult from jnt_letter join letter_deb using (jl_id) where j_id = a;
   if NOT FOUND then
	select jl_id into nResult from jnt_letter join letter_cred using (jl_id) where j_id = a;
	if NOT found then
		return null;
	end if;
    end if;
return nResult;
end;
$$;



CREATE FUNCTION comptaproc.get_menu_dependency(profile_menu_id integer) RETURNS SETOF integer
    LANGUAGE plpgsql
    AS $$
declare
	i int;
	x int;
	e int;
begin
	for x in select pm_id,me_code
			from profile_menu
			where me_code_dep in (select me_code from profile_menu where pm_id=profile_menu_id)
			and p_id = (select p_id from profile_menu where pm_id=profile_menu_id)
	loop
		return next x;

	for e in select *  from comptaproc.get_menu_dependency(x)
		loop
			return next e;
		end loop;

	end loop;
	return;
end;
$$;



CREATE FUNCTION comptaproc.get_menu_tree(p_code text, p_profile integer) RETURNS SETOF public.menu_tree
    LANGUAGE plpgsql
    AS $$
declare
	i menu_tree;
	e menu_tree;
	a text;
	x v_all_menu%ROWTYPE;
begin
	for x in select *  from v_all_menu where me_code_dep=p_code::text and p_id=p_profile
	loop
		if x.me_code_dep is not null then
			i.code := x.me_code_dep||'/'||x.me_code;
		else
			i.code := x.me_code;
		end if;

		i.description := x.me_description;

		return next i;

	for e in select *  from get_menu_tree(x.me_code,p_profile)
		loop
			e.code:=x.me_code_dep||'/'||e.code;
			return next e;
		end loop;

	end loop;
	return;
end;
$$;



CREATE FUNCTION comptaproc.get_pcm_tree(source public.account_type) RETURNS SETOF public.account_type
    LANGUAGE plpgsql
    AS $$
declare
	i account_type;
	e account_type;
begin
	for i in select pcm_val from tmp_pcmn where pcm_val_parent=source
	loop
		return next i;
		for e in select get_pcm_tree from get_pcm_tree(i)
		loop
			return next e;
		end loop;

	end loop;
	return;
end;
$$;



CREATE FUNCTION comptaproc.get_profile_menu(p_profile integer) RETURNS SETOF public.menu_tree
    LANGUAGE plpgsql
    AS $$
declare
	a menu_tree;
	e menu_tree;
begin
for a in select me_code,me_description from v_all_menu where p_id=p_profile
	and me_code_dep is null and me_type <> 'PR' and me_type <>'SP'
loop
		return next a;

		for e in select * from get_menu_tree(a.code,p_profile)
		loop
			return next e;
		end loop;

	end loop;
return;
end;
$$;



CREATE FUNCTION comptaproc.group_analytic_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
name text;
begin
name:=upper(NEW.ga_id);
name:=trim(name);
name:=replace(name,' ','');
NEW.ga_id:=name;
return NEW;
end;$$;



CREATE FUNCTION comptaproc.group_analytique_del() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
update poste_analytique set ga_id=null
where ga_id=OLD.ga_id;
return OLD;
end;$$;



CREATE FUNCTION comptaproc.html_quote(p_string text) RETURNS text
    LANGUAGE plpgsql
    AS $$
declare
	r text;
begin
	r:=p_string;
	r:=replace(r,'<','&lt;');
	r:=replace(r,'>','&gt;');
	r:=replace(r,'''','&quot;');
	return r;
end;$$;



COMMENT ON FUNCTION comptaproc.html_quote(p_string text) IS 'remove harmfull HTML char';



CREATE FUNCTION comptaproc.info_def_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
	row_info_def info_def%ROWTYPE;
	str_type text;
begin
row_info_def:=NEW;
str_type:=upper(trim(NEW.id_type));
str_type:=replace(str_type,' ','');
str_type:=replace(str_type,',','');
str_type:=replace(str_type,';','');
if  length(str_type) =0 then
	raise exception 'id_type cannot be null';
end if;
row_info_def.id_type:=str_type;
return row_info_def;
end;
$$;



CREATE FUNCTION comptaproc.insert_jrnx(p_date character varying, p_montant numeric, p_poste public.account_type, p_grpt integer, p_jrn_def integer, p_debit boolean, p_tech_user text, p_tech_per integer, p_qcode text, p_comment text) RETURNS void
    LANGUAGE plpgsql
    AS $$
begin
	insert into jrnx
	(
		j_date,
		j_montant,
		j_poste,
		j_grpt,
		j_jrn_def,
		j_debit,
		j_text,
		j_tech_user,
		j_tech_per,
		j_qcode
	) values
	(
		to_date(p_date,'DD.MM.YYYY'),
		p_montant,
		p_poste,
		p_grpt,
		p_jrn_def,
		p_debit,
		p_comment,
		p_tech_user,
		p_tech_per,
		p_qcode
	);

return;
end;
$$;



CREATE FUNCTION comptaproc.insert_quant_purchase(p_internal text, p_j_id numeric, p_fiche character varying, p_quant numeric, p_price numeric, p_vat numeric, p_vat_code integer, p_nd_amount numeric, p_nd_tva numeric, p_nd_tva_recup numeric, p_dep_priv numeric, p_client character varying, p_tva_sided numeric, p_price_unit numeric) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
        fid_client integer;
        fid_good   integer;
        account_priv    account_type;
        fid_good_account account_type;
        n_dep_priv numeric;
begin
        n_dep_priv := p_dep_priv;
        select p_value into account_priv from parm_code where p_code='DEP_PRIV';
        select f_id into fid_client from
                fiche_detail where ad_id=23 and ad_value=upper(trim(p_client));
        select f_id into fid_good from
                 fiche_detail where ad_id=23 and ad_value=upper(trim(p_fiche));
        select ad_value into fid_good_account from fiche_detail where ad_id=5 and f_id=fid_good;
        if strpos( fid_good_account , account_priv ) = 1 then
                n_dep_priv=p_price;
        end if; 
            
        insert into quant_purchase
                (qp_internal,
                j_id,
                qp_fiche,
                qp_quantite,
                qp_price,
                qp_vat,
                qp_vat_code,
                qp_nd_amount,
                qp_nd_tva,
                qp_nd_tva_recup,
                qp_supplier,
                qp_dep_priv,
                qp_vat_sided,
                qp_unit)
        values
                (p_internal,
                p_j_id,
                fid_good,
                p_quant,
                p_price,
                p_vat,
                p_vat_code,
                p_nd_amount,
                p_nd_tva,
                p_nd_tva_recup,
                fid_client,
                n_dep_priv,
                p_tva_sided,
                p_price_unit);
        return;
end;
$$;



CREATE FUNCTION comptaproc.insert_quant_sold(p_internal text, p_jid numeric, p_fiche character varying, p_quant numeric, p_price numeric, p_vat numeric, p_vat_code integer, p_client character varying, p_tva_sided numeric, p_price_unit numeric) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
        fid_client integer;
        fid_good   integer;
begin

        select f_id into fid_client from
                fiche_detail where ad_id=23 and ad_value=upper(trim(p_client));
        select f_id into fid_good from
                fiche_detail where ad_id=23 and ad_value=upper(trim(p_fiche));
        insert into quant_sold
                (qs_internal,j_id,qs_fiche,qs_quantite,qs_price,qs_vat,qs_vat_code,qs_client,qs_valid,qs_vat_sided,qs_unit)
        values
                (p_internal,p_jid,fid_good,p_quant,p_price,p_vat,p_vat_code,fid_client,'Y',p_tva_sided,p_price_unit);
        return;
end;
 $$;



CREATE FUNCTION comptaproc.insert_quick_code(nf_id integer, tav_text text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    ns integer;
    nExist integer;
    tText text;
    tBase text;
    tName text;
    nCount Integer;
    nDuplicate Integer;
begin
    tText := comptaproc.format_quickcode(tav_text);
    nDuplicate := 0;
    tBase := tText;
    -- take the next sequence
    select nextval('s_jnt_fic_att_value') into ns;
    loop
        if length (tText) = 0 or tText is null then
            select count(*) into nCount from fiche_detail where f_id=nf_id and ad_id=1;
            if nCount = 0 then
                tBase := 'CRD';
            else
                select ad_value into tName from fiche_detail where f_id=nf_id and ad_id=1;
                tName := comptaproc.format_quickcode(tName);
                tName := substr(tName,1,6);
                tBase := tName;
                if nDuplicate = 0 then
                    tText := tName;
                else
                    tText := tBase||nDuplicate::text;
                end if;
            end if;
        end if;
        if coalesce(tText,'') = '' then
            tText := 'CRD';
        end if;
        -- av_text already used ?
        select count(*) into nExist
        from fiche_detail
        where
                ad_id=23 and  ad_value=tText;

        if nExist = 0 then
            exit;
        end if;
        nDuplicate := nDuplicate + 1 ;
        tText := tBase || nDuplicate::text;

        if nDuplicate > 99999 then
            raise Exception 'too many duplicate % duplicate# %',tText,nDuplicate;
        end if;
    end loop;


    insert into fiche_detail(jft_id,f_id,ad_id,ad_value) values (ns,nf_id,23,upper(tText));
    return ns;
end;
$$;



CREATE FUNCTION comptaproc.is_closed(p_periode integer, p_jrn_def_id integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
bClosed bool;
str_status text;
begin
select p_closed into bClosed from parm_periode
	where p_id=p_periode;

if bClosed = true then
	return bClosed;
end if;

select status into str_status from jrn_periode
       where p_id =p_periode and jrn_def_id=p_jrn_def_id;

if str_status <> 'OP' then
   return bClosed;
end if;
return false;
end;
$$;



CREATE FUNCTION comptaproc.jnt_fic_attr_ins() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
   r_record jnt_fic_attr%ROWTYPE;
   i_max integer;
begin
r_record=NEW;
perform comptaproc.fiche_attribut_synchro(r_record.fd_id);
select coalesce(max(jnt_order),0) into i_max from jnt_fic_attr where fd_id=r_record.fd_id;
i_max := i_max + 10;
NEW.jnt_order=i_max;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.jrn_add_note(p_jrid bigint, p_note text) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	tmp bigint;
begin
	if length(trim(p_note)) = 0 then
	   delete from jrn_note where jr_id= p_jrid;
	   return;
	end if;
	
	select n_id into tmp from jrn_note where jr_id = p_jrid;
	
	if FOUND then
	   update jrn_note set n_text=trim(p_note) where jr_id = p_jrid;
	else 
	   insert into jrn_note (jr_id,n_text) values ( p_jrid, p_note);

	end if;
	
	return;
end;
$$;



CREATE FUNCTION comptaproc.jrn_check_periode() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
bClosed bool;
str_status text;
ljr_tech_per jrn.jr_tech_per%TYPE;
ljr_def_id jrn.jr_def_id%TYPE;
lreturn jrn%ROWTYPE;
begin
if TG_OP='UPDATE' then
    ljr_tech_per :=OLD.jr_tech_per ;
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=NEW;
    if NEW.jr_date = OLD.jr_date then
        return NEW;
    end if;
    if comptaproc.is_closed(NEW.jr_tech_per,NEW.jr_def_id) = true then
              raise exception 'Periode fermee';
    end if;
end if;

if TG_OP='INSERT' then
    NEW.jr_tech_per := comptaproc.find_periode(to_char(NEW.jr_date,'DD.MM.YYYY'));
    ljr_tech_per :=NEW.jr_tech_per ;
    ljr_def_id :=NEW.jr_def_id;
    lreturn :=NEW;
end if;

if TG_OP='DELETE' then
    ljr_tech_per :=OLD.jr_tech_per;
    ljr_def_id :=OLD.jr_def_id;
    lreturn :=OLD;
end if;

if comptaproc.is_closed (ljr_tech_per,ljr_def_id) = true then
       raise exception 'Periode fermee';
end if;

return lreturn;
end;
$$;



CREATE FUNCTION comptaproc.jrn_currency() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin 
	if new.currency_id is null then 
		new.currency_id := 0;
                new.currency_rate := 1;
                new.currency_rate_ref := 1;
	end if;
	return new;
end;
$$;



CREATE FUNCTION comptaproc.jrn_def_add() RETURNS trigger
    LANGUAGE plpgsql
    AS $$begin
execute 'insert into jrn_periode(p_id,jrn_def_id,status) select p_id,'||NEW.jrn_def_id||',
	case when p_central=true then ''CE''
	      when p_closed=true then ''CL''
	else ''OP''
	end
from
parm_periode ';
return NEW;
end;$$;



CREATE FUNCTION comptaproc.jrn_def_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
nb numeric;
begin
select count(*) into nb from jrn where jr_def_id=OLD.jrn_def_id;

if nb <> 0 then
	raise exception 'EFFACEMENT INTERDIT: JOURNAL UTILISE';
end if;
return OLD;
end;$$;



CREATE FUNCTION comptaproc.jrn_del() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
row jrn%ROWTYPE;
begin
row:=OLD;
insert into del_jrn ( jr_id,
       jr_def_id,
       jr_montant,
       jr_comment,
       jr_date,
       jr_grpt_id,
       jr_internal,
       jr_tech_date,
       jr_tech_per,
       jrn_ech,
       jr_ech,
       jr_rapt,
       jr_valid,
       jr_opid,
       jr_c_opid,
       jr_pj,
       jr_pj_name,
       jr_pj_type,
       jr_pj_number,
       del_jrn_date) 
       select  jr_id,
	      jr_def_id,
	      jr_montant,
	      jr_comment,
	      jr_date,
	      jr_grpt_id,
	      jr_internal,
	      jr_tech_date,
	      jr_tech_per,
	      jrn_ech,
	      jr_ech,
	      jr_rapt,
	      jr_valid,
	      jr_opid,
	      jr_c_opid,
	      jr_pj,
	      jr_pj_name,
	      jr_pj_type,
	      jr_pj_number
	      ,now() from jrn where jr_id=row.jr_id;
return row;
end;
$$;



CREATE FUNCTION comptaproc.jrnx_del() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
row jrnx%ROWTYPE;
begin
row:=OLD;


insert into del_jrnx(
            j_id, j_date, j_montant, j_poste, j_grpt, j_rapt, j_jrn_def, 
            j_debit, j_text, j_centralized, j_internal, j_tech_user, j_tech_date, 
            j_tech_per, j_qcode, f_id)  SELECT j_id, j_date, j_montant, j_poste, j_grpt, j_rapt, j_jrn_def, 
       j_debit, j_text, j_centralized, j_internal, j_tech_user, j_tech_date, 
       j_tech_per, j_qcode, f_id from jrnx where j_id=row.j_id;
return row;
end;
$$;



CREATE FUNCTION comptaproc.jrnx_ins() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
n_fid bigint;
nCount integer;
sQcode text;

begin
n_fid := NULL;
sQcode := NULL;

NEW.j_tech_per := comptaproc.find_periode(to_char(NEW.j_date,'DD.MM.YYYY'));
if NEW.j_tech_per = -1 then
        raise exception 'Période invalide';
end if;

if trim(coalesce(NEW.j_qcode,'')) = '' then
        -- how many card has this accounting
        select count(*) into nCount from fiche_detail where ad_id=5 and ad_value=NEW.j_poste;
        -- only one card is found , then we change the j_qcode by the card
        if nCount = 1 then
                select f_id into n_fid from fiche_detail where ad_id = 5 and ad_value=NEW.j_poste;
            if FOUND then
                select ad_value into sQcode  from fiche_detail where f_id=n_fid and ad_id = 23;
                NEW.f_id := n_fid;
                NEW.j_qcode = sQcode;
                raise info 'comptaproc.jrnx_ins : found card % qcode %',n_fid,sQcode;
            end if;
        end if;

end if;

NEW.j_qcode=trim(upper(coalesce(NEW.j_qcode,'')));

if length (coalesce(NEW.j_qcode,'')) = 0 then
    NEW.j_qcode=NULL;
    else
      select f_id into n_fid from fiche_detail  where ad_id=23 and ad_value=NEW.j_qcode;
      if NOT FOUND then
        raise exception 'La fiche dont le quick code est % n''existe pas',NEW.j_qcode;
    end if;
end if;

NEW.f_id:=n_fid;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.jrnx_letter_del() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
row jrnx%ROWTYPE;
begin
row:=OLD;
delete from jnt_letter 
	where (jl_id in (select jl_id from letter_deb) and jl_id not in(select jl_id from letter_cred )) 
		or (jl_id not in (select jl_id from letter_deb  ) and jl_id  in(select jl_id from letter_cred ));
return row;
end;
$$;



CREATE FUNCTION comptaproc.menu_complete_dependency(n_profile numeric) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
 n_count integer;
 csr_root_menu cursor (p_profile numeric) is select pm_id,
	me_code,
	me_code_dep 
	
	from profile_menu 
	where 
	me_code in 
		(select a.me_code_dep 
			from profile_menu as a 
			join profile_menu as b on (a.me_code=b.me_code and a.me_code_dep=b.me_code_dep and a.pm_id <> b.pm_id and a.p_id=b.p_id) 
			where a.p_id=n_profile) 
		and p_id=p_profile;

begin
	for duplicate in csr_root_menu(n_profile)
	loop
		raise notice 'found %',duplicate;
		update profile_menu set pm_id_dep  = duplicate.pm_id 
			where pm_id in (select a.pm_id
				from profile_menu as a 
				left join profile_menu as b on (a.me_code=b.me_code and a.me_code_dep=b.me_code_dep)
				where 
				a.p_id=n_profile
				and b.p_id=n_profile
				and a.pm_id_dep is null 
				and a.me_code_dep = duplicate.me_code
				and a.pm_id < b.pm_id);
	end loop;
	
	for duplicate in csr_root_menu(n_profile) 
	loop
		select count(*) into n_count from profile_menu where p_id=n_profile and pm_id_dep = duplicate.pm_id;
		raise notice '% use % times',duplicate,n_count;
		if n_count = 0 then
			raise notice ' Update with %',duplicate;
			update profile_menu set pm_id_dep = duplicate.pm_id where p_id = n_profile and me_code_dep = duplicate.me_code and pm_id_dep is null;
		end if;

	end loop;
	
end;
$$;



CREATE FUNCTION comptaproc.opd_limit_description() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare
		sDescription text;
	begin
	sDescription := NEW.od_description;
	NEW.od_description := substr(sDescription,1,80);
	return NEW;
	end;
$$;



CREATE FUNCTION comptaproc.periode_exist(p_date text, p_periode_id bigint) RETURNS integer
    LANGUAGE plpgsql
    AS $$

declare n_p_id int4;
begin

select p_id into n_p_id
        from parm_periode
        where
                p_start <= to_date(p_date,'DD.MM.YYYY')
                and
                p_end >= to_date(p_date,'DD.MM.YYYY')
                and
                p_id <> p_periode_id;

if NOT FOUND then
        return -1;
end if;

return n_p_id;

end;$$;



CREATE FUNCTION comptaproc.plan_analytic_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
   name text;
begin
   name:=upper(NEW.pa_name);
   name:=trim(name);
   name:=replace(name,' ','');
   NEW.pa_name:=name;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.poste_analytique_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$declare
name text;
rCount record;

begin
name:=upper(NEW.po_name);
name:=trim(name);
name:=replace(name,' ','');		
NEW.po_name:=name;

if NEW.ga_id is NULL then
return NEW;
end if;

if length(trim(NEW.ga_id)) = 0 then
  NEW.ga_id:=NULL;
  return NEW;
end if;
perform 'select ga_id from groupe_analytique where ga_id='||NEW.ga_id;
if NOT FOUND then
   raise exception' Inexistent Group Analytic %',NEW.ga_id;
end if;
return NEW;
end;$$;



CREATE FUNCTION comptaproc.proc_check_balance() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
	diff numeric;
	tt integer;
begin
	if TG_OP = 'INSERT' or TG_OP='UPDATE' then
	tt=NEW.jr_grpt_id;
	diff:=check_balance(tt);
	if diff != 0 then
		raise exception 'balance error %',diff ;
	end if;
	return NEW;
	end if;
end;
$$;



CREATE FUNCTION comptaproc.quant_purchase_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	begin
		if NEW.qp_price < 0 OR NEW.qp_quantite <0 THEN
			NEW.qp_price := abs (NEW.qp_price)*(-1);
			NEW.qp_quantite := abs (NEW.qp_quantite)*(-1);
		end if;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.quant_sold_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	begin
		if NEW.qs_price < 0 OR NEW.qs_quantite <0 THEN
			NEW.qs_price := abs (NEW.qs_price)*(-1);
			NEW.qs_quantite := abs (NEW.qs_quantite)*(-1);
		end if;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.set_tech_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
    /* variable */
    noalyss_user text;
begin
    new.tech_user := current_setting('noalyss.user_login');
    new.tech_date := now();
    return NEW;

exception when others then
    new.tech_date := now();
    new.tech_user := current_user;
    return NEW;
end ;
$$;



CREATE FUNCTION comptaproc.t_document_modele_validate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
    lText text;
    modified document_modele%ROWTYPE;
begin
    modified:=NEW;

	modified.md_filename:=replace(NEW.md_filename,' ','_');
	return modified;
end;
$$;



CREATE FUNCTION comptaproc.t_document_type_insert() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
nCounter integer;
    BEGIN
select count(*) into nCounter from pg_class where relname='seq_doc_type_'||NEW.dt_id;
if nCounter = 0 then
        execute  'create sequence seq_doc_type_'||NEW.dt_id;
end if;
        RETURN NEW;
    END;
$$;



CREATE FUNCTION comptaproc.t_document_validate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
  lText text;
  modified document%ROWTYPE;
begin
    	modified:=NEW;
	modified.d_filename:=replace(NEW.d_filename,' ','_');
	return modified;
end;
$$;



CREATE FUNCTION comptaproc.t_jrn_def_description() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    declare
        str varchar(200);
    BEGIN
        str := substr(NEW.jrn_def_description,1,200);
        NEW.jrn_def_description := str;

        RETURN NEW;
    END;
$$;



CREATE FUNCTION comptaproc.t_jrn_def_sequence() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
nCounter integer;

    BEGIN
    select count(*) into nCounter 
       from pg_class where relname='s_jrn_'||NEW.jrn_def_id;
       if nCounter = 0 then
       	   execute  'create sequence s_jrn_'||NEW.jrn_def_id;
	   raise notice 'Creating sequence s_jrn_%',NEW.jrn_def_id;
	 end if;

        RETURN NEW;
    END;
$$;



CREATE FUNCTION comptaproc.t_parameter_extra_code() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
	new.pe_code := comptaproc.transform_to_code (new.pe_code);
        return new;
end;
$$;



CREATE FUNCTION comptaproc.tmp_pcmn_alphanum_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
   r_record tmp_pcmn%ROWTYPE;
begin
r_record := NEW;
r_record.pcm_val:=format_account(NEW.pcm_val);

return r_record;
end;
$$;



CREATE FUNCTION comptaproc.tmp_pcmn_ins() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
   r_record tmp_pcmn%ROWTYPE;
begin
r_record := NEW;
if  length(trim(r_record.pcm_type))=0 or r_record.pcm_type is NULL then
   r_record.pcm_type:=find_pcm_type(NEW.pcm_val);
   return r_record;
end if;
return NEW;
end;
$$;



CREATE FUNCTION comptaproc.transform_to_code(p_account text) RETURNS text
    LANGUAGE plpgsql
    AS $_$

declare

sResult text;

begin
sResult := lower(p_account);

sResult := translate(sResult,E'éèêëàâäïîüûùöôç','eeeeaaaiiuuuooc');
sResult := translate(sResult,E' $€µ£%.+-/\\!(){}(),;&|"#''^<>*','');

return upper(sResult);

end;
$_$;



CREATE FUNCTION comptaproc.trg_profile_user_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

NEW.user_name := lower(NEW.user_name);
return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trg_remove_script_tag() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

    NEW.agc_comment_raw:= regexp_replace(NEW.agc_comment_raw, '<script', 'scritp', 'i');
    return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trg_todo_list_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

NEW.use_login:= lower(NEW.use_login);
return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trg_todo_list_shared_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

NEW.use_login:= lower(NEW.use_login);
return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trg_user_sec_act_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

NEW.ua_login:= lower(NEW.ua_login);
return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trg_user_sec_jrn_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

begin

NEW.uj_login:= lower(NEW.uj_login);
return NEW;

end;
$$;



CREATE FUNCTION comptaproc.trim_cvs_quote() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
        modified import_tmp%ROWTYPE;
begin
	modified:=NEW;
	modified.devise=replace(new.devise,'"','');
	modified.poste_comptable=replace(new.poste_comptable,'"','');
        modified.compte_ordre=replace(NEW.COMPTE_ORDRE,'"','');
        modified.detail=replace(NEW.DETAIL,'"','');
        modified.num_compte=replace(NEW.NUM_COMPTE,'"','');
        return modified;
end;
$$;



CREATE FUNCTION comptaproc.trim_space_format_csv_banque() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
        modified format_csv_banque%ROWTYPE;
begin
        modified.name=trim(NEW.NAME);
        modified.include_file=trim(new.include_file);
		if ( length(modified.name) = 0 ) then
			modified.name=null;
		end if;
		if ( length(modified.include_file) = 0 ) then
			modified.include_file=null;
		end if;

        return modified;
end;
$$;



CREATE FUNCTION comptaproc.tva_delete(integer) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
declare
	p_tva_id alias for $1;
	nCount integer;
begin
	nCount=0;
	select count(*) into nCount from quant_sold where qs_vat_code=p_tva_id;
	if nCount != 0 then
                 return;
		
	end if;
	select count(*) into nCount from quant_purchase where qp_vat_code=p_tva_id;
	if nCount != 0 then
                 return;
		
	end if;

delete from tva_rate where tva_id=p_tva_id;
	return;
end;
$_$;



CREATE FUNCTION comptaproc.tva_insert(text, numeric, text, text, integer) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
declare
	l_tva_id integer;
	p_tva_label alias for $1;
	p_tva_rate alias for $2;
	p_tva_comment alias for $3;
	p_tva_poste alias for $4;
	p_tva_both_side alias for $5;
	debit text;
	credit text;
	nCount integer;
begin
if length(trim(p_tva_label)) = 0 then
	return 3;
end if;

if length(trim(p_tva_poste)) != 0 then
	if position (',' in p_tva_poste) = 0 then return 4; end if;
	debit  = split_part(p_tva_poste,',',1);
	credit	= split_part(p_tva_poste,',',2);
	select count(*) into nCount from tmp_pcmn where pcm_val=debit::account_type;
	if nCount = 0 then return 4; end if;
	select count(*) into nCount from tmp_pcmn where pcm_val=credit::account_type;
	if nCount = 0 then return 4; end if;

end if;
select into l_tva_id nextval('s_tva') ;
insert into tva_rate(tva_id,tva_label,tva_rate,tva_comment,tva_poste,tva_both_side)
	values (l_tva_id,p_tva_label,p_tva_rate,p_tva_comment,p_tva_poste,p_tva_both_side);
return 0;
end;
$_$;



CREATE FUNCTION comptaproc.tva_modify(integer, text, numeric, text, text, integer) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
declare
	p_tva_id alias for $1;
	p_tva_label alias for $2;
	p_tva_rate alias for $3;
	p_tva_comment alias for $4;
	p_tva_poste alias for $5;
	p_tva_both_side alias for $6;
	debit text;
	credit text;
	nCount integer;
begin
if length(trim(p_tva_label)) = 0 then
	return 3;
end if;

if length(trim(p_tva_poste)) != 0 then
	if position (',' in p_tva_poste) = 0 then return 4; end if;
	debit  = split_part(p_tva_poste,',',1);
	credit	= split_part(p_tva_poste,',',2);
	select count(*) into nCount from tmp_pcmn where pcm_val=debit::account_type;
	if nCount = 0 then return 4; end if;
	select count(*) into nCount from tmp_pcmn where pcm_val=credit::account_type;
	if nCount = 0 then return 4; end if;

end if;
update tva_rate set tva_label=p_tva_label,tva_rate=p_tva_rate,tva_comment=p_tva_comment,tva_poste=p_tva_poste,tva_both_side=p_tva_both_side
	where tva_id=p_tva_id;
return 0;
end;
$_$;



CREATE FUNCTION comptaproc.update_quick_code(njft_id integer, tav_text text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    ns integer;
    nExist integer;
    tText text;
    tBase text;
    old_qcode varchar;
    num_rows_jrnx integer;
    num_rows_predef integer;
    n_count integer;
begin
    n_count := 0;
    -- get current value
    select ad_value into old_qcode from fiche_detail where jft_id=njft_id;
    -- av_text didn't change so no update
    if tav_text = upper( trim(old_qcode)) then
        raise notice 'nothing to change % %' , tav_text,old_qcode;
        return 0;
    end if;

    tText := comptaproc.format_quickcode(tav_text);

    if length ( tText) = 0 or tText is null then
        return 0;
    end if;

    ns := njft_id;
    tBase := tText;
    loop
        -- av_text already used ?
        select count(*) into nExist
        from fiche_detail
        where
                ad_id=23 and ad_value=tText
          and jft_id <> njft_id;

        if nExist = 0 then
            exit;
        end if;
        tText := tBase || n_count::text;
        n_count := n_count + 1 ;

    end loop;
    update fiche_detail set ad_value = tText where jft_id=njft_id;

    -- update also the contact
    update fiche_detail set ad_value = tText
    where jft_id in
          ( select jft_id
            from fiche_detail
            where ad_id in (select ad_id from attr_def where ad_type='card') and ad_value=old_qcode);


    return ns;
end;
$$;



COMMENT ON FUNCTION comptaproc.update_quick_code(njft_id integer, tav_text text) IS 'update the qcode + related columns in other cards';



CREATE FUNCTION public.bud_card_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$declare
 sCode text;
begin

sCode:=trim(upper(NEW.bc_code));
sCode:=replace(sCode,' ','_');
sCode:=substr(sCode,1,10);
NEW.bc_code:=sCode;
return NEW;
end;$$;



CREATE FUNCTION public.bud_detail_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$declare
mline bud_detail%ROWTYPE;
begin
mline:=NEW;
if mline.po_id = -1 then
   mline.po_id:=NULL;
end if;
return mline;
end;$$;



CREATE FUNCTION public.correct_quant_purchase() RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	r_invalid quant_purchase;
	s_QuickCode text;
	b_j_debit bool;
	r_new record;
	r_jrnx record;
begin

for r_invalid in select * from quant_purchase where qp_valid='A'
loop

select j_qcode into s_QuickCode from vw_poste_qcode where f_id=r_invalid.qp_fiche;
raise notice 'qp_id % Quick code is %',r_invalid.qp_id,s_QuickCode;

select j_debit,j_grpt,j_jrn_def,j_montant into r_jrnx from jrnx where j_id=r_invalid.j_id;
if NOT FOUND then
	raise notice 'error not found jrnx %',r_invalid.j_id;
	update quant_purchase set qp_valid='Y' where qp_id=r_invalid.qp_id;
	continue;
end if;
raise notice 'j_debit % , j_grpt % ,j_jrn_def  % qp_price %',r_jrnx.j_debit,r_jrnx.j_grpt,r_jrnx.j_jrn_def ,r_invalid.qp_price;

select jr_internal,j_id,j_montant into r_new
	from jrnx join jrn on (j_grpt=jr_grpt_id)
	where 
	j_jrn_def=r_jrnx.j_jrn_def
	and j_id not in (select j_id from  quant_purchase)
	and j_qcode=s_QuickCode
	and j_montant=r_jrnx.j_montant
	and j_debit != r_jrnx.j_debit;

if NOT FOUND then
	raise notice 'error not found %', r_invalid.j_id;
	update quant_purchase set qp_valid='Y' where qp_id=r_invalid.qp_id;
	continue;     
end if;
raise notice 'j_id % found amount %',r_new.j_id,r_new.j_montant;

insert into quant_purchase (qp_internal,j_id,qp_fiche,qp_quantite,qp_price,qp_vat,qp_nd_amount,qp_nd_tva_recup,qp_valid,qp_dep_priv,qp_supplier,qp_vat_code)
values (r_new.jr_internal,r_invalid.j_id,r_invalid.qp_fiche,(r_invalid.qp_quantite * (-1)),r_invalid.qp_price * (-1),r_invalid.qp_vat*(-1),r_invalid.qp_nd_amount*(-1),r_invalid.qp_nd_tva_recup*(-1) ,'Y',r_invalid.qp_dep_priv*(-1),r_invalid.qp_supplier,r_invalid.qp_vat_code);

update quant_purchase set qp_valid='Y' where qp_id=r_invalid.qp_id;
end loop;
return;
end;
$$;



CREATE FUNCTION public.correct_quant_sale() RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	r_invalid quant_sold;
	s_QuickCode text;
	b_j_debit bool;
	r_new record;
	r_jrnx record;
begin

for r_invalid in select * from quant_sold where qs_valid='A'
loop

select j_qcode into s_QuickCode from vw_poste_qcode where f_id=r_invalid.qs_fiche;
raise notice 'qp_id % Quick code is %',r_invalid.qs_id,s_QuickCode;

select j_debit,j_grpt,j_jrn_def,j_montant into r_jrnx from jrnx where j_id=r_invalid.j_id;
if NOT FOUND then
	update quant_sold set qs_valid='Y' where qs_id=r_invalid.qs_id;
	raise notice 'error not found jrnx %',r_invalid.j_id;
	continue;
end if;
raise notice 'j_debit % , j_grpt % ,j_jrn_def  % qs_price %',r_jrnx.j_debit,r_jrnx.j_grpt,r_jrnx.j_jrn_def ,r_invalid.qs_price;

select jr_internal,j_id,j_montant into r_new
	from jrnx join jrn on (j_grpt=jr_grpt_id)
	where 
	j_jrn_def=r_jrnx.j_jrn_def
	and j_id not in (select j_id from  quant_sold)
	and j_qcode=s_QuickCode
	and j_montant=r_jrnx.j_montant
	and j_debit != r_jrnx.j_debit;

if NOT FOUND then
   update quant_sold set qs_valid='Y' where qs_id=r_invalid.qs_id;
	raise notice 'error not found %', r_invalid.j_id;
	continue;
end if;
raise notice 'j_id % found amount %',r_new.j_id,r_new.j_montant;


 insert into quant_sold (qs_internal,j_id,qs_fiche,qs_quantite,qs_price,qs_vat,qs_valid,qs_client,qs_vat_code)
 values (r_new.jr_internal,r_invalid.j_id,r_invalid.qs_fiche,(r_invalid.qs_quantite * (-1)),r_invalid.qs_price * (-1),r_invalid.qs_vat*(-1),'Y',r_invalid.qs_client,r_invalid.qs_vat_code);
 update quant_sold set qs_valid='Y' where qs_id=r_invalid.qs_id;
end loop;
return;
end;
$$;



CREATE FUNCTION public.isdate(text, text) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
DECLARE x timestamp;
BEGIN
    x := to_date($1,$2);
    RETURN TRUE;
EXCEPTION WHEN others THEN
    RETURN FALSE;
END;
$_$;



CREATE FUNCTION public.modify_menu_system(n_profile numeric) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
r_duplicate profile_menu%ROWTYPE;
str_duplicate text;
n_lowest_id numeric; -- lowest pm_id : update the dependency in profile_menu
n_highest_id numeric; -- highest pm_id insert into profile_menu

begin

for str_duplicate in   
	select me_code 
	from profile_menu 
	where 
	p_id=n_profile and 
	p_type_display <> 'P' and
	pm_id_dep is null
	group by me_code 
	having count(*) > 1 
loop
	raise info 'str_duplicate %',str_duplicate;
	for r_duplicate in select * 
		from profile_menu 
		where 
		p_id=n_profile and
		me_code_dep=str_duplicate
	loop
		raise info 'r_duplicate %',r_duplicate;
		-- get the lowest 
		select a.pm_id into n_lowest_id from profile_menu a join profile_menu b on (a.me_code=b.me_code and a.p_id = b.p_id)
		where
		a.me_code=str_duplicate
		and a.p_id=n_profile
		and a.pm_id < b.pm_id;
		raise info 'lowest is %',n_lowest_id;
		-- get the highest
		select a.pm_id into n_highest_id from profile_menu a join profile_menu b on (a.me_code=b.me_code and a.p_id = b.p_id)
		where
		a.me_code=str_duplicate
		and a.p_id=n_profile
		and a.pm_id > b.pm_id;
		raise info 'highest is %',n_highest_id;

		-- update the first one
		update profile_menu set pm_id_dep = n_lowest_id where pm_id=r_duplicate.pm_id;
		-- insert a new one
		insert into profile_menu (me_code,
			me_code_dep,
			p_id,
			p_order,
			p_type_display,
			pm_default,
			pm_id_dep)
		values (r_duplicate.me_code,
			r_duplicate.me_code_dep,
			r_duplicate.p_id,
			r_duplicate.p_order,
			r_duplicate.p_type_display,
			r_duplicate.pm_default,
			n_highest_id);
		
	end loop;	

end loop;	
end;
$$;



CREATE FUNCTION public.replace_menu_code(code_source text, code_destination text) RETURNS void
    LANGUAGE plpgsql
    AS $$
begin
    /*code */

    update bookmark set b_action = replace(b_action,code_source,code_destination) where b_action ~ code_source;
    update menu_ref set me_code =code_destination where me_code = code_source;
    update profile_menu set me_code=code_destination where me_code = code_source;
    update profile_menu set me_code_dep=code_destination where me_code_dep = code_source;
end ;
$$;



CREATE FUNCTION public.upgrade_repo(p_version integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare 
        is_mono integer;
begin
        select count (*) into is_mono from information_schema.tables where table_name='repo_version';
        if is_mono = 1 then
                update repo_version set val=p_version;
        else
                update version set val=p_version;
        end if;
end;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;


CREATE TABLE public.acc_other_tax (
    ac_id integer NOT NULL,
    ac_label text NOT NULL,
    ac_rate numeric(5,2) NOT NULL,
    ajrn_def_id integer[],
    ac_accounting public.account_type NOT NULL
);



COMMENT ON TABLE public.acc_other_tax IS 'Additional tax for Sale or Purchase ';



COMMENT ON COLUMN public.acc_other_tax.ac_label IS 'Label of the tax';



COMMENT ON COLUMN public.acc_other_tax.ac_rate IS 'rate of the tax in percent';



COMMENT ON COLUMN public.acc_other_tax.ajrn_def_id IS 'array of to FK jrn_def (jrn_def_id)';



COMMENT ON COLUMN public.acc_other_tax.ac_accounting IS 'FK tmp_pcmn (pcm_val)';



CREATE SEQUENCE public.acc_other_tax_ac_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.acc_other_tax_ac_id_seq OWNED BY public.acc_other_tax.ac_id;



CREATE TABLE public.action (
    ac_id integer NOT NULL,
    ac_description text NOT NULL,
    ac_module text,
    ac_code character varying(30)
);



COMMENT ON TABLE public.action IS 'The different privileges';



COMMENT ON COLUMN public.action.ac_code IS 'this code will be used in the code with the function User::check_action ';



CREATE TABLE public.action_comment_document (
    acd_id bigint NOT NULL,
    document_id bigint,
    action_gestion_comment_id bigint
);



ALTER TABLE public.action_comment_document ALTER COLUMN acd_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.action_comment_document_acd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE TABLE public.action_detail (
    ad_id integer NOT NULL,
    f_id bigint,
    ad_text text,
    ad_pu numeric(20,4) DEFAULT 0,
    ad_quant numeric(20,4) DEFAULT 0,
    ad_tva_id integer DEFAULT 0,
    ad_tva_amount numeric(20,4) DEFAULT 0,
    ad_total_amount numeric(20,4) DEFAULT 0,
    ag_id integer DEFAULT 0 NOT NULL
);



COMMENT ON TABLE public.action_detail IS 'Detail of action_gestion, see class Action_Detail';



COMMENT ON COLUMN public.action_detail.f_id IS 'the concerned	card';



COMMENT ON COLUMN public.action_detail.ad_text IS ' Description ';



COMMENT ON COLUMN public.action_detail.ad_pu IS ' price per unit ';



COMMENT ON COLUMN public.action_detail.ad_quant IS 'quantity ';



COMMENT ON COLUMN public.action_detail.ad_tva_id IS ' tva_id ';



COMMENT ON COLUMN public.action_detail.ad_tva_amount IS ' tva_amount ';



COMMENT ON COLUMN public.action_detail.ad_total_amount IS ' total amount';



CREATE SEQUENCE public.action_detail_ad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_detail_ad_id_seq OWNED BY public.action_detail.ad_id;



CREATE SEQUENCE public.action_gestion_ag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.action_gestion (
    ag_id integer DEFAULT nextval('public.action_gestion_ag_id_seq'::regclass) NOT NULL,
    ag_type integer,
    f_id_dest integer,
    ag_title text,
    ag_timestamp timestamp without time zone DEFAULT now(),
    ag_ref text,
    ag_hour text,
    ag_priority integer DEFAULT 2,
    ag_dest bigint DEFAULT '-1'::integer NOT NULL,
    ag_owner text,
    ag_contact bigint,
    ag_state integer,
    ag_remind_date date
);



COMMENT ON TABLE public.action_gestion IS 'Contains the details for the follow-up of customer, supplier, administration';



COMMENT ON COLUMN public.action_gestion.ag_type IS ' type of action: see document_type ';



COMMENT ON COLUMN public.action_gestion.f_id_dest IS 'third party';



COMMENT ON COLUMN public.action_gestion.ag_title IS 'title';



COMMENT ON COLUMN public.action_gestion.ag_ref IS 'its reference';



COMMENT ON COLUMN public.action_gestion.ag_priority IS 'Low, medium, important';



COMMENT ON COLUMN public.action_gestion.ag_dest IS 'is the profile which has to take care of this action';



COMMENT ON COLUMN public.action_gestion.ag_owner IS 'is the owner of this action';



COMMENT ON COLUMN public.action_gestion.ag_contact IS 'contact of the third part';



COMMENT ON COLUMN public.action_gestion.ag_state IS 'state of the action same as document_state';



CREATE TABLE public.action_gestion_comment (
    agc_id bigint NOT NULL,
    ag_id bigint,
    agc_date timestamp with time zone DEFAULT now(),
    agc_comment text,
    tech_user text,
    agc_comment_raw text
);



COMMENT ON TABLE public.action_gestion_comment IS 'comment on action management';



COMMENT ON COLUMN public.action_gestion_comment.agc_id IS 'PK';



COMMENT ON COLUMN public.action_gestion_comment.ag_id IS 'FK to action_gestion';



COMMENT ON COLUMN public.action_gestion_comment.agc_comment IS 'comment';



COMMENT ON COLUMN public.action_gestion_comment.tech_user IS 'user_login';



CREATE SEQUENCE public.action_gestion_comment_agc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_gestion_comment_agc_id_seq OWNED BY public.action_gestion_comment.agc_id;



CREATE TABLE public.action_gestion_filter (
    af_id bigint NOT NULL,
    af_user text NOT NULL,
    af_name text NOT NULL,
    af_search text NOT NULL
);



ALTER TABLE public.action_gestion_filter ALTER COLUMN af_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.action_gestion_filter_af_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE TABLE public.action_gestion_operation (
    ago_id bigint NOT NULL,
    ag_id bigint,
    jr_id bigint
);



COMMENT ON TABLE public.action_gestion_operation IS 'Operation linked on action';



COMMENT ON COLUMN public.action_gestion_operation.ago_id IS 'pk';



COMMENT ON COLUMN public.action_gestion_operation.ag_id IS 'fk to action_gestion';



COMMENT ON COLUMN public.action_gestion_operation.jr_id IS 'fk to jrn';



CREATE SEQUENCE public.action_gestion_operation_ago_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_gestion_operation_ago_id_seq OWNED BY public.action_gestion_operation.ago_id;



CREATE TABLE public.action_gestion_related (
    aga_id bigint NOT NULL,
    aga_least bigint NOT NULL,
    aga_greatest bigint NOT NULL,
    aga_type bigint
);



COMMENT ON TABLE public.action_gestion_related IS 'link between action';



COMMENT ON COLUMN public.action_gestion_related.aga_id IS 'pk';



COMMENT ON COLUMN public.action_gestion_related.aga_least IS 'fk to action_gestion, smallest ag_id';



COMMENT ON COLUMN public.action_gestion_related.aga_greatest IS 'fk to action_gestion greatest ag_id';



COMMENT ON COLUMN public.action_gestion_related.aga_type IS 'Type de liens';



CREATE SEQUENCE public.action_gestion_related_aga_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_gestion_related_aga_id_seq OWNED BY public.action_gestion_related.aga_id;



CREATE TABLE public.action_person (
    ap_id integer NOT NULL,
    ag_id integer NOT NULL,
    f_id integer NOT NULL
);



COMMENT ON TABLE public.action_person IS 'Person involved in the action';



COMMENT ON COLUMN public.action_person.ap_id IS 'pk';



COMMENT ON COLUMN public.action_person.ag_id IS 'fk to fiche';



CREATE SEQUENCE public.action_person_ap_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_person_ap_id_seq OWNED BY public.action_person.ap_id;



CREATE TABLE public.action_person_option (
    ap_id bigint NOT NULL,
    ap_value character varying,
    contact_option_ref_id bigint NOT NULL,
    action_person_id bigint NOT NULL
);



COMMENT ON TABLE public.action_person_option IS 'option for each contact';



COMMENT ON COLUMN public.action_person_option.ap_value IS 'Value of the option';



COMMENT ON COLUMN public.action_person_option.contact_option_ref_id IS 'FK to contact_option';



COMMENT ON COLUMN public.action_person_option.action_person_id IS 'FK to action_person';



CREATE SEQUENCE public.action_person_option_ap_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_person_option_ap_id_seq OWNED BY public.action_person_option.ap_id;



CREATE TABLE public.action_tags (
    at_id integer NOT NULL,
    t_id integer,
    ag_id integer
);



COMMENT ON TABLE public.action_tags IS 'Tags link to  action';



CREATE SEQUENCE public.action_tags_at_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.action_tags_at_id_seq OWNED BY public.action_tags.at_id;



CREATE TABLE public.attr_def (
    ad_id integer DEFAULT nextval(('s_attr_def'::text)::regclass) NOT NULL,
    ad_text text,
    ad_type text,
    ad_size text NOT NULL,
    ad_extra text,
    ad_search_followup integer DEFAULT 1,
    ad_default_order integer
);



COMMENT ON TABLE public.attr_def IS 'The available attributs for the cards';



COMMENT ON COLUMN public.attr_def.ad_search_followup IS '1 : search  available  from followup , 0  : search not available in followup';



COMMENT ON COLUMN public.attr_def.ad_default_order IS 'Default order of the attribute';



CREATE TABLE public.attr_min (
    frd_id integer NOT NULL,
    ad_id integer NOT NULL,
    ad_default_order integer
);



COMMENT ON TABLE public.attr_min IS 'The value of  attributs for the cards';



CREATE SEQUENCE public.bilan_b_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.bilan (
    b_id integer DEFAULT nextval('public.bilan_b_id_seq'::regclass) NOT NULL,
    b_name text NOT NULL,
    b_file_template text NOT NULL,
    b_file_form text,
    b_type text NOT NULL
);



COMMENT ON TABLE public.bilan IS 'contains the template and the data for generating different documents  ';



COMMENT ON COLUMN public.bilan.b_id IS 'primary key';



COMMENT ON COLUMN public.bilan.b_name IS 'Name of the document';



COMMENT ON COLUMN public.bilan.b_file_template IS 'path of the template (document/...)';



COMMENT ON COLUMN public.bilan.b_file_form IS 'path of the file with forms';



COMMENT ON COLUMN public.bilan.b_type IS 'type = ODS, RTF...';



CREATE TABLE public.bookmark (
    b_id integer NOT NULL,
    b_order integer DEFAULT 1,
    b_action text,
    login text
);



COMMENT ON TABLE public.bookmark IS 'Bookmark of the connected user';



CREATE SEQUENCE public.bookmark_b_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.bookmark_b_id_seq OWNED BY public.bookmark.b_id;



CREATE SEQUENCE public.bud_card_bc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.bud_detail_bd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.bud_detail_periode_bdp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.centralized (
    c_id integer DEFAULT nextval(('s_centralized'::text)::regclass) NOT NULL,
    c_j_id integer,
    c_date date NOT NULL,
    c_internal text NOT NULL,
    c_montant numeric(20,4) NOT NULL,
    c_debit boolean DEFAULT true,
    c_jrn_def integer NOT NULL,
    c_poste public.account_type,
    c_description text,
    c_grp integer NOT NULL,
    c_comment text,
    c_rapt text,
    c_periode integer,
    c_order integer
);



COMMENT ON TABLE public.centralized IS 'The centralized journal';



CREATE TABLE public.contact_option_ref (
    cor_id bigint NOT NULL,
    cor_label character varying NOT NULL,
    cor_type integer DEFAULT 0 NOT NULL,
    cor_value_select character varying
);



COMMENT ON TABLE public.contact_option_ref IS 'Option for the contact';



COMMENT ON COLUMN public.contact_option_ref.cor_label IS 'Label de l''option';



COMMENT ON COLUMN public.contact_option_ref.cor_type IS '0 text , 1 select ,2 nombre , 3 date';



COMMENT ON COLUMN public.contact_option_ref.cor_value_select IS 'Select values';



CREATE SEQUENCE public.contact_option_ref_cor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.contact_option_ref_cor_id_seq OWNED BY public.contact_option_ref.cor_id;



CREATE TABLE public.currency (
    id integer NOT NULL,
    cr_code_iso character varying(10),
    cr_name character varying(80)
);



COMMENT ON TABLE public.currency IS 'currency';



COMMENT ON COLUMN public.currency.cr_code_iso IS 'Code ISO';



COMMENT ON COLUMN public.currency.cr_name IS 'Name of the currency';



CREATE TABLE public.currency_history (
    id integer NOT NULL,
    ch_value numeric(20,8) NOT NULL,
    ch_from date NOT NULL,
    currency_id integer NOT NULL,
    CONSTRAINT currency_history_check CHECK ((ch_value > (0)::numeric))
);



COMMENT ON TABLE public.currency_history IS 'currency values history';



COMMENT ON COLUMN public.currency_history.id IS 'pk';



COMMENT ON COLUMN public.currency_history.ch_value IS 'rate of currency depending of currency of the folder';



COMMENT ON COLUMN public.currency_history.ch_from IS 'Date when the rate is available';



COMMENT ON COLUMN public.currency_history.currency_id IS 'FK to currency';



CREATE SEQUENCE public.currency_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.currency_history_id_seq OWNED BY public.currency_history.id;



CREATE SEQUENCE public.currency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.currency_id_seq OWNED BY public.currency.id;



CREATE TABLE public.del_action (
    del_id integer NOT NULL,
    del_name text NOT NULL,
    del_time timestamp without time zone
);



COMMENT ON TABLE public.del_action IS 'deleted actions';



CREATE SEQUENCE public.del_action_del_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.del_action_del_id_seq OWNED BY public.del_action.del_id;



CREATE TABLE public.del_jrn (
    jr_id integer NOT NULL,
    jr_def_id integer,
    jr_montant numeric(20,4),
    jr_comment text,
    jr_date date,
    jr_grpt_id integer,
    jr_internal text,
    jr_tech_date timestamp without time zone,
    jr_tech_per integer,
    jrn_ech date,
    jr_ech date,
    jr_rapt text,
    jr_valid boolean,
    jr_opid integer,
    jr_c_opid integer,
    jr_pj oid,
    jr_pj_name text,
    jr_pj_type text,
    del_jrn_date timestamp without time zone,
    jr_pj_number text,
    dj_id integer NOT NULL
);



COMMENT ON TABLE public.del_jrn IS 'deleted operation';



CREATE SEQUENCE public.del_jrn_dj_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.del_jrn_dj_id_seq OWNED BY public.del_jrn.dj_id;



CREATE TABLE public.del_jrnx (
    j_id integer NOT NULL,
    j_date date,
    j_montant numeric(20,4),
    j_poste public.account_type,
    j_grpt integer,
    j_rapt text,
    j_jrn_def integer,
    j_debit boolean,
    j_text text,
    j_centralized boolean,
    j_internal text,
    j_tech_user text,
    j_tech_date timestamp without time zone,
    j_tech_per integer,
    j_qcode text,
    djx_id integer NOT NULL,
    f_id bigint
);



COMMENT ON TABLE public.del_jrnx IS 'delete operation details';



CREATE SEQUENCE public.del_jrnx_djx_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.del_jrnx_djx_id_seq OWNED BY public.del_jrnx.djx_id;



CREATE SEQUENCE public.document_d_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.document (
    d_id integer DEFAULT nextval('public.document_d_id_seq'::regclass) NOT NULL,
    ag_id integer NOT NULL,
    d_lob oid,
    d_number bigint NOT NULL,
    d_filename text,
    d_mimetype text,
    d_description text
);



COMMENT ON TABLE public.document IS 'This table contains all the documents : summary and lob files';



CREATE TABLE public.document_component (
    dc_id integer NOT NULL,
    dc_code text NOT NULL,
    dc_comment text NOT NULL
);



COMMENT ON TABLE public.document_component IS 'Give the component of NOALYSS that is using is';



COMMENT ON COLUMN public.document_component.dc_id IS 'PK';



COMMENT ON COLUMN public.document_component.dc_code IS 'Code used in document_modele';



COMMENT ON COLUMN public.document_component.dc_comment IS 'Code used in document_modele';



ALTER TABLE public.document_component ALTER COLUMN dc_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.document_component_dc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE SEQUENCE public.document_modele_md_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.document_modele (
    md_id integer DEFAULT nextval('public.document_modele_md_id_seq'::regclass) NOT NULL,
    md_name text NOT NULL,
    md_lob oid,
    md_type integer NOT NULL,
    md_filename text,
    md_mimetype text,
    md_affect character varying(3) NOT NULL
);



COMMENT ON TABLE public.document_modele IS ' contains all the template for the  documents';



CREATE TABLE public.document_option (
    do_id bigint NOT NULL,
    do_code character varying(20) NOT NULL,
    document_type_id bigint,
    do_enable integer DEFAULT 1 NOT NULL,
    do_option character varying
);



COMMENT ON TABLE public.document_option IS 'Reference of option addable to document_type';



COMMENT ON COLUMN public.document_option.do_code IS 'Code of the option to add';



COMMENT ON COLUMN public.document_option.document_type_id IS 'FK to document_type';



COMMENT ON COLUMN public.document_option.do_enable IS '1 the option is activated, 0 is inativated';



COMMENT ON COLUMN public.document_option.do_option IS 'Option for the detail';



CREATE SEQUENCE public.document_option_do_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.document_option_do_id_seq OWNED BY public.document_option.do_id;



CREATE SEQUENCE public.document_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



COMMENT ON SEQUENCE public.document_seq IS 'Sequence for the sequence bound to the document modele';



CREATE SEQUENCE public.document_state_s_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.document_state (
    s_id integer DEFAULT nextval('public.document_state_s_id_seq'::regclass) NOT NULL,
    s_value character varying(50) NOT NULL,
    s_status character(1)
);



COMMENT ON TABLE public.document_state IS 'State of the document';



CREATE SEQUENCE public.document_type_dt_id_seq
    START WITH 25
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.document_type (
    dt_id integer DEFAULT nextval('public.document_type_dt_id_seq'::regclass) NOT NULL,
    dt_value character varying(80),
    dt_prefix text
);



COMMENT ON TABLE public.document_type IS 'Type of document : meeting, invoice,...';



COMMENT ON COLUMN public.document_type.dt_prefix IS 'Prefix for ag_ref';



CREATE TABLE public.extension (
    ex_id integer NOT NULL,
    ex_name character varying(30) NOT NULL,
    ex_code character varying(15) NOT NULL,
    ex_desc character varying(250),
    ex_file character varying NOT NULL,
    ex_enable "char" DEFAULT 'Y'::"char" NOT NULL
);



COMMENT ON TABLE public.extension IS 'Content the needed information for the extension';



COMMENT ON COLUMN public.extension.ex_id IS 'Primary key';



COMMENT ON COLUMN public.extension.ex_name IS 'code of the extension ';



COMMENT ON COLUMN public.extension.ex_code IS 'code of the extension ';



COMMENT ON COLUMN public.extension.ex_desc IS 'Description of the extension ';



COMMENT ON COLUMN public.extension.ex_file IS 'path to the extension to include';



COMMENT ON COLUMN public.extension.ex_enable IS 'Y : enabled N : disabled ';



CREATE SEQUENCE public.extension_ex_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.extension_ex_id_seq OWNED BY public.extension.ex_id;



CREATE TABLE public.fiche (
    f_id integer DEFAULT nextval(('s_fiche'::text)::regclass) NOT NULL,
    fd_id integer,
    f_enable character(1) NOT NULL,
    CONSTRAINT f_enable_ck CHECK ((f_enable = ANY (ARRAY['0'::bpchar, '1'::bpchar])))
);



COMMENT ON TABLE public.fiche IS 'Cards';



COMMENT ON COLUMN public.fiche.f_enable IS 'value = 1 if card enable , otherwise 0 ';



CREATE TABLE public.fiche_def (
    fd_id integer DEFAULT nextval(('s_fdef'::text)::regclass) NOT NULL,
    fd_class_base text,
    fd_label text NOT NULL,
    fd_create_account boolean DEFAULT false,
    frd_id integer NOT NULL,
    fd_description text
);



COMMENT ON TABLE public.fiche_def IS 'Cards definition';



CREATE TABLE public.fiche_def_ref (
    frd_id integer DEFAULT nextval(('s_fiche_def_ref'::text)::regclass) NOT NULL,
    frd_text text,
    frd_class_base public.account_type
);



COMMENT ON TABLE public.fiche_def_ref IS 'Family Cards definition';



CREATE TABLE public.fiche_detail (
    jft_id integer DEFAULT nextval(('s_jnt_fic_att_value'::text)::regclass) NOT NULL,
    f_id integer,
    ad_id integer,
    ad_value text
);



COMMENT ON TABLE public.fiche_detail IS 'join between the card and the attribut definition';



CREATE TABLE public.forecast (
    f_id integer NOT NULL,
    f_name text NOT NULL,
    f_start_date bigint,
    f_end_date bigint
);



COMMENT ON TABLE public.forecast IS 'contains the name of the forecast';



CREATE TABLE public.forecast_category (
    fc_id integer NOT NULL,
    fc_desc text NOT NULL,
    f_id bigint NOT NULL,
    fc_order integer DEFAULT 0 NOT NULL
);



COMMENT ON TABLE public.forecast_category IS 'Category of forecast';



COMMENT ON COLUMN public.forecast_category.fc_id IS 'primary key';



COMMENT ON COLUMN public.forecast_category.fc_desc IS 'text of the category';



COMMENT ON COLUMN public.forecast_category.f_id IS 'Foreign key, it is the parent from the table forecast';



COMMENT ON COLUMN public.forecast_category.fc_order IS 'Order of the category, used when displaid';



CREATE SEQUENCE public.forecast_cat_fc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.forecast_cat_fc_id_seq OWNED BY public.forecast_category.fc_id;



CREATE SEQUENCE public.forecast_f_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.forecast_f_id_seq OWNED BY public.forecast.f_id;



CREATE TABLE public.forecast_item (
    fi_id integer NOT NULL,
    fi_text text,
    fi_account text,
    fi_order integer,
    fc_id integer,
    fi_amount numeric(20,4) DEFAULT 0,
    fi_pid integer,
    fi_amount_initial numeric(20,4) DEFAULT 0
);



COMMENT ON TABLE public.forecast_item IS 'items of forecast';



COMMENT ON COLUMN public.forecast_item.fi_id IS 'Primary key';



COMMENT ON COLUMN public.forecast_item.fi_text IS 'Label of the i	tem';



COMMENT ON COLUMN public.forecast_item.fi_account IS 'Accountancy entry';



COMMENT ON COLUMN public.forecast_item.fi_order IS 'Order of showing (not used)';



COMMENT ON COLUMN public.forecast_item.fi_amount IS 'Amount';



COMMENT ON COLUMN public.forecast_item.fi_pid IS '0 for every month, or the value parm_periode.p_id ';



CREATE SEQUENCE public.forecast_item_fi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.forecast_item_fi_id_seq OWNED BY public.forecast_item.fi_id;



CREATE TABLE public.form_definition (
    fr_id integer DEFAULT nextval(('s_formdef'::text)::regclass) NOT NULL,
    fr_label text
);



COMMENT ON TABLE public.form_definition IS 'Simple Report name';



CREATE TABLE public.form_detail (
    fo_id integer DEFAULT nextval(('s_form'::text)::regclass) NOT NULL,
    fo_fr_id integer NOT NULL,
    fo_pos integer,
    fo_label text,
    fo_formula text
);



COMMENT ON TABLE public.form_detail IS 'Simple report details with formula';



CREATE TABLE public.groupe_analytique (
    ga_id character varying(10) NOT NULL,
    pa_id integer,
    ga_description text
);



COMMENT ON TABLE public.groupe_analytique IS 'Group of analytic accountancy';



CREATE SEQUENCE public.historique_analytique_ha_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.info_def (
    id_type text NOT NULL,
    id_description text
);



COMMENT ON TABLE public.info_def IS 'Contains the types of additionnal info we can add to a operation';



CREATE TABLE public.jnt_document_option_contact (
    jdoc_id bigint NOT NULL,
    jdoc_enable integer NOT NULL,
    document_type_id bigint,
    contact_option_ref_id bigint,
    CONSTRAINT jnt_document_option_contact_check CHECK ((jdoc_enable = ANY (ARRAY[0, 1])))
);



COMMENT ON TABLE public.jnt_document_option_contact IS 'Many to many table between document and contact option';



CREATE SEQUENCE public.jnt_document_option_contact_jdoc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.jnt_document_option_contact_jdoc_id_seq OWNED BY public.jnt_document_option_contact.jdoc_id;



CREATE SEQUENCE public.s_jnt_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.jnt_fic_attr (
    fd_id integer,
    ad_id integer,
    jnt_id bigint DEFAULT nextval('public.s_jnt_id'::regclass) NOT NULL,
    jnt_order integer NOT NULL
);



COMMENT ON TABLE public.jnt_fic_attr IS 'join between the family card and the attribut definition';



CREATE TABLE public.jnt_letter (
    jl_id integer NOT NULL
);



COMMENT ON TABLE public.jnt_letter IS 'm2m tables for lettering';



CREATE SEQUENCE public.jnt_letter_jl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.jnt_letter_jl_id_seq OWNED BY public.jnt_letter.jl_id;



CREATE TABLE public.jnt_tag_group_tag (
    tag_group_id bigint NOT NULL,
    tag_id bigint NOT NULL,
    jt_id integer NOT NULL
);



COMMENT ON TABLE public.jnt_tag_group_tag IS 'Many to Many table betwwen tag and tag group';



CREATE SEQUENCE public.jnt_tag_group_tag_jt_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.jnt_tag_group_tag_jt_id_seq OWNED BY public.jnt_tag_group_tag.jt_id;



CREATE TABLE public.jrn (
    jr_id integer DEFAULT nextval(('s_jrn'::text)::regclass) NOT NULL,
    jr_def_id integer NOT NULL,
    jr_montant numeric(20,4) NOT NULL,
    jr_comment text,
    jr_date date,
    jr_grpt_id integer NOT NULL,
    jr_internal text,
    jr_tech_date timestamp without time zone DEFAULT now() NOT NULL,
    jr_tech_per integer NOT NULL,
    jrn_ech date,
    jr_ech date,
    jr_rapt text,
    jr_valid boolean DEFAULT true,
    jr_opid integer,
    jr_c_opid integer,
    jr_pj oid,
    jr_pj_name text,
    jr_pj_type text,
    jr_pj_number text,
    jr_mt text,
    jr_date_paid date,
    jr_optype character varying(3) DEFAULT 'NOR'::character varying,
    currency_id bigint DEFAULT 0 NOT NULL,
    currency_rate numeric(20,6) DEFAULT 1 NOT NULL,
    currency_rate_ref numeric(20,6) DEFAULT 1 NOT NULL
);



COMMENT ON TABLE public.jrn IS 'Journal: content one line for a group of accountancy writing';



COMMENT ON COLUMN public.jrn.jr_optype IS 'Type of operation , NOR = NORMAL , OPE opening , EXT extourne, CLO closing';



CREATE TABLE public.jrn_def (
    jrn_def_id integer DEFAULT nextval(('s_jrn_def'::text)::regclass) NOT NULL,
    jrn_def_name text NOT NULL,
    jrn_def_class_deb text,
    jrn_def_class_cred text,
    jrn_def_fiche_deb text,
    jrn_def_fiche_cred text,
    jrn_deb_max_line integer DEFAULT 1,
    jrn_cred_max_line integer DEFAULT 1,
    jrn_def_ech boolean DEFAULT false,
    jrn_def_ech_lib text,
    jrn_def_type character(3) NOT NULL,
    jrn_def_code text NOT NULL,
    jrn_def_pj_pref text,
    jrn_def_bank bigint,
    jrn_def_num_op integer,
    jrn_def_description text,
    jrn_enable integer DEFAULT 1,
    currency_id integer DEFAULT 0 NOT NULL,
    jrn_def_negative_amount character(1) DEFAULT '0'::bpchar NOT NULL,
    jrn_def_negative_warning text,
    jrn_def_quantity smallint DEFAULT 1 NOT NULL,
    jrn_def_pj_padding integer DEFAULT 0,
    CONSTRAINT negative_amount_ck CHECK ((jrn_def_negative_amount = ANY (ARRAY['1'::bpchar, '0'::bpchar])))
);



COMMENT ON TABLE public.jrn_def IS 'Definition of a journal, his properties';



COMMENT ON COLUMN public.jrn_def.jrn_enable IS 'Set to 1 if the ledger is enable ';



COMMENT ON COLUMN public.jrn_def.currency_id IS 'Default currency for financial ledger';



COMMENT ON COLUMN public.jrn_def.jrn_def_negative_amount IS '1 echo a warning if you are not using an negative amount, default 0 for no warning';



COMMENT ON COLUMN public.jrn_def.jrn_def_negative_warning IS 'Yell a warning if the amount if not negative , in the case of jrn_def_negative_amount is Y';



COMMENT ON COLUMN public.jrn_def.jrn_def_quantity IS 'Use the quantity column, 0->disable,1->enable,used only with Sale and Purchase otherwise ignored';



CREATE TABLE public.jrn_info (
    ji_id integer NOT NULL,
    jr_id integer NOT NULL,
    id_type text NOT NULL,
    ji_value text
);



COMMENT ON TABLE public.jrn_info IS 'extra info for operation';



CREATE SEQUENCE public.jrn_info_ji_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.jrn_info_ji_id_seq OWNED BY public.jrn_info.ji_id;



CREATE TABLE public.jrn_note (
    n_id integer NOT NULL,
    n_text text,
    jr_id bigint NOT NULL
);



COMMENT ON TABLE public.jrn_note IS 'Note about operation';



CREATE SEQUENCE public.jrn_note_n_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.jrn_note_n_id_seq OWNED BY public.jrn_note.n_id;



CREATE SEQUENCE public.jrn_periode_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.jrn_periode (
    jrn_def_id integer NOT NULL,
    p_id integer NOT NULL,
    status text,
    id bigint DEFAULT nextval('public.jrn_periode_id_seq'::regclass) NOT NULL
);



COMMENT ON TABLE public.jrn_periode IS 'Period by ledger';



CREATE TABLE public.jrn_rapt (
    jra_id integer DEFAULT nextval(('s_jrn_rapt'::text)::regclass) NOT NULL,
    jr_id integer NOT NULL,
    jra_concerned integer NOT NULL
);



COMMENT ON TABLE public.jrn_rapt IS 'Rapprochement between operation';



CREATE TABLE public.jrn_tax (
    jt_id integer NOT NULL,
    j_id bigint NOT NULL,
    pcm_val public.account_type NOT NULL,
    ac_id integer NOT NULL
);



COMMENT ON COLUMN public.jrn_tax.j_id IS 'fk jrnx';



COMMENT ON COLUMN public.jrn_tax.pcm_val IS 'FK tmp_pcmn';



COMMENT ON COLUMN public.jrn_tax.ac_id IS 'FK to acc_other_tax';



ALTER TABLE public.jrn_tax ALTER COLUMN jt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.jrn_tax_jt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE TABLE public.jrn_type (
    jrn_type_id character(3) NOT NULL,
    jrn_desc text
);



COMMENT ON TABLE public.jrn_type IS 'Type of journal (Sell, Buy, Financial...)';



CREATE TABLE public.jrnx (
    j_id integer DEFAULT nextval(('s_jrn_op'::text)::regclass) NOT NULL,
    j_date date DEFAULT now(),
    j_montant numeric(20,4) DEFAULT 0,
    j_poste public.account_type NOT NULL,
    j_grpt integer NOT NULL,
    j_rapt text,
    j_jrn_def integer NOT NULL,
    j_debit boolean DEFAULT true,
    j_text text,
    j_centralized boolean DEFAULT false,
    j_internal text,
    j_tech_user text NOT NULL,
    j_tech_date timestamp without time zone DEFAULT now() NOT NULL,
    j_tech_per integer NOT NULL,
    j_qcode text,
    f_id bigint
);



COMMENT ON TABLE public.jrnx IS 'Journal: content one line for each accountancy writing';



CREATE TABLE public.key_distribution (
    kd_id integer NOT NULL,
    kd_name text,
    kd_description text
);



COMMENT ON TABLE public.key_distribution IS 'Distribution key for analytic';



COMMENT ON COLUMN public.key_distribution.kd_id IS 'PK';



COMMENT ON COLUMN public.key_distribution.kd_name IS 'Name of the key';



COMMENT ON COLUMN public.key_distribution.kd_description IS 'Description of the key';



CREATE TABLE public.key_distribution_activity (
    ka_id integer NOT NULL,
    ke_id bigint NOT NULL,
    po_id bigint,
    pa_id bigint NOT NULL
);



COMMENT ON TABLE public.key_distribution_activity IS 'Contains the analytic account';



COMMENT ON COLUMN public.key_distribution_activity.ka_id IS 'pk';



COMMENT ON COLUMN public.key_distribution_activity.ke_id IS 'fk to key_distribution_detail';



COMMENT ON COLUMN public.key_distribution_activity.po_id IS 'fk to poste_analytique';



COMMENT ON COLUMN public.key_distribution_activity.pa_id IS 'fk to plan_analytique';



CREATE SEQUENCE public.key_distribution_activity_ka_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.key_distribution_activity_ka_id_seq OWNED BY public.key_distribution_activity.ka_id;



CREATE TABLE public.key_distribution_detail (
    ke_id integer NOT NULL,
    kd_id bigint NOT NULL,
    ke_row integer NOT NULL,
    ke_percent numeric(20,4) NOT NULL
);



COMMENT ON TABLE public.key_distribution_detail IS 'Row of activity and percent';



COMMENT ON COLUMN public.key_distribution_detail.ke_id IS 'pk';



COMMENT ON COLUMN public.key_distribution_detail.kd_id IS 'fk to key_distribution';



COMMENT ON COLUMN public.key_distribution_detail.ke_row IS 'group order';



CREATE SEQUENCE public.key_distribution_detail_ke_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.key_distribution_detail_ke_id_seq OWNED BY public.key_distribution_detail.ke_id;



CREATE SEQUENCE public.key_distribution_kd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.key_distribution_kd_id_seq OWNED BY public.key_distribution.kd_id;



CREATE TABLE public.key_distribution_ledger (
    kl_id integer NOT NULL,
    kd_id bigint NOT NULL,
    jrn_def_id bigint NOT NULL
);



COMMENT ON TABLE public.key_distribution_ledger IS 'Legder where the distribution key can be used';



COMMENT ON COLUMN public.key_distribution_ledger.kl_id IS 'pk';



COMMENT ON COLUMN public.key_distribution_ledger.kd_id IS 'fk to key_distribution';



COMMENT ON COLUMN public.key_distribution_ledger.jrn_def_id IS 'fk to jrnd_def, ledger where this key is available';



CREATE SEQUENCE public.key_distribution_ledger_kl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.key_distribution_ledger_kl_id_seq OWNED BY public.key_distribution_ledger.kl_id;



CREATE TABLE public.letter_cred (
    lc_id integer NOT NULL,
    j_id bigint NOT NULL,
    jl_id bigint NOT NULL
);



COMMENT ON TABLE public.letter_cred IS 'Letter cred';



CREATE SEQUENCE public.letter_cred_lc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.letter_cred_lc_id_seq OWNED BY public.letter_cred.lc_id;



CREATE TABLE public.letter_deb (
    ld_id integer NOT NULL,
    j_id bigint NOT NULL,
    jl_id bigint NOT NULL
);



COMMENT ON TABLE public.letter_deb IS 'letter deb';



CREATE SEQUENCE public.letter_deb_ld_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.letter_deb_ld_id_seq OWNED BY public.letter_deb.ld_id;



CREATE TABLE public.link_action_type (
    l_id bigint NOT NULL,
    l_desc character varying
);



CREATE SEQUENCE public.link_action_type_l_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.link_action_type_l_id_seq OWNED BY public.link_action_type.l_id;



CREATE TABLE public.menu_default (
    md_id integer NOT NULL,
    md_code text NOT NULL,
    me_code text NOT NULL
);



COMMENT ON TABLE public.menu_default IS 'default menu for certains actions';



CREATE SEQUENCE public.menu_default_md_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.menu_default_md_id_seq OWNED BY public.menu_default.md_id;



CREATE TABLE public.menu_ref (
    me_code text NOT NULL,
    me_menu text,
    me_file text,
    me_url text,
    me_description text,
    me_parameter text,
    me_javascript text,
    me_type character varying(2),
    me_description_etendue text
);



COMMENT ON TABLE public.menu_ref IS 'Definition of all the menu';



COMMENT ON COLUMN public.menu_ref.me_code IS 'Menu Code ';



COMMENT ON COLUMN public.menu_ref.me_menu IS 'Label to display';



COMMENT ON COLUMN public.menu_ref.me_file IS 'if not empty file to include';



COMMENT ON COLUMN public.menu_ref.me_url IS 'url ';



COMMENT ON COLUMN public.menu_ref.me_type IS 'ME for menu
PR for Printing
SP for special meaning (ex: return to line)
PL for plugin';



CREATE TABLE public.payment_method (
    mp_id integer NOT NULL,
    mp_lib text NOT NULL,
    mp_jrn_def_id integer NOT NULL,
    mp_fd_id bigint,
    mp_qcode text,
    jrn_def_id bigint
);



COMMENT ON TABLE public.payment_method IS 'Contains the different media of payment and the corresponding ledger';



COMMENT ON COLUMN public.payment_method.jrn_def_id IS 'Ledger using this payment method';



CREATE SEQUENCE public.mod_payment_mp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.mod_payment_mp_id_seq OWNED BY public.payment_method.mp_id;



CREATE SEQUENCE public.op_def_op_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.op_predef (
    od_id integer DEFAULT nextval('public.op_def_op_seq'::regclass) NOT NULL,
    jrn_def_id integer NOT NULL,
    od_name text NOT NULL,
    od_item integer NOT NULL,
    od_jrn_type text NOT NULL,
    od_direct boolean NOT NULL,
    od_description text
);



COMMENT ON TABLE public.op_predef IS 'predefined operation';



COMMENT ON COLUMN public.op_predef.jrn_def_id IS 'jrn_id';



COMMENT ON COLUMN public.op_predef.od_name IS 'name of the operation';



CREATE SEQUENCE public.op_predef_detail_opd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.op_predef_detail (
    opd_id integer DEFAULT nextval('public.op_predef_detail_opd_id_seq'::regclass) NOT NULL,
    od_id integer NOT NULL,
    opd_poste text NOT NULL,
    opd_amount numeric(20,4),
    opd_tva_id text,
    opd_quantity numeric(20,4),
    opd_debit boolean NOT NULL,
    opd_tva_amount numeric(20,4),
    opd_comment text,
    opd_qc boolean
);



COMMENT ON TABLE public.op_predef_detail IS 'contains the detail of predefined operations';



CREATE SEQUENCE public.s_oa_group
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.operation_analytique (
    oa_id integer DEFAULT nextval('public.historique_analytique_ha_id_seq'::regclass) NOT NULL,
    po_id integer NOT NULL,
    oa_amount numeric(20,4) NOT NULL,
    oa_description text,
    oa_debit boolean DEFAULT true NOT NULL,
    j_id integer,
    oa_group integer DEFAULT nextval('public.s_oa_group'::regclass) NOT NULL,
    oa_date date NOT NULL,
    oa_row integer,
    oa_jrnx_id_source bigint,
    oa_positive character(1) DEFAULT 'Y'::bpchar NOT NULL,
    f_id bigint,
    CONSTRAINT operation_analytique_oa_amount_check CHECK ((oa_amount >= (0)::numeric))
);



COMMENT ON TABLE public.operation_analytique IS 'History of the analytic account';



COMMENT ON COLUMN public.operation_analytique.oa_jrnx_id_source IS 'jrnx.j_id source of this amount, this amount is computed from an amount giving a ND VAT.Normally NULL  is there is no ND VAT.';



COMMENT ON COLUMN public.operation_analytique.oa_positive IS 'Sign of the amount';



COMMENT ON COLUMN public.operation_analytique.f_id IS 'FK to fiche.f_id , used only with ODS';



CREATE TABLE public.operation_currency (
    id bigint NOT NULL,
    oc_amount numeric(20,6) NOT NULL,
    oc_vat_amount numeric(20,6) DEFAULT 0,
    oc_price_unit numeric(20,6),
    j_id bigint NOT NULL
);



COMMENT ON TABLE public.operation_currency IS 'Information about currency';



COMMENT ON COLUMN public.operation_currency.oc_amount IS 'amount in currency';



COMMENT ON COLUMN public.operation_currency.oc_vat_amount IS 'vat amount in currency';



COMMENT ON COLUMN public.operation_currency.oc_price_unit IS 'unit price in currency';



COMMENT ON COLUMN public.operation_currency.j_id IS 'fk to jrnx';



CREATE SEQUENCE public.operation_currency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.operation_currency_id_seq OWNED BY public.operation_currency.id;



CREATE TABLE public.operation_exercice (
    oe_id bigint NOT NULL,
    oe_date date,
    oe_type text NOT NULL,
    oe_text text,
    oe_dossier_id integer NOT NULL,
    oe_exercice integer NOT NULL,
    jr_internal text,
    oe_transfer_date timestamp without time zone,
    tech_user text,
    tech_date timestamp without time zone DEFAULT now(),
    CONSTRAINT operation_exercice_oe_type_check CHECK (((oe_type = 'opening'::text) OR (oe_type = 'closing'::text)))
);



CREATE TABLE public.operation_exercice_detail (
    oed_id bigint NOT NULL,
    oe_id bigint NOT NULL,
    oed_poste public.account_type,
    oed_qcode text,
    oed_label text,
    oed_amount numeric(20,4),
    oed_debit boolean
);



ALTER TABLE public.operation_exercice_detail ALTER COLUMN oed_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.operation_exercice_detail_oed_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



ALTER TABLE public.operation_exercice ALTER COLUMN oe_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.operation_exercice_oe_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE TABLE public.operation_tag (
    opt_id bigint NOT NULL,
    jrn_id bigint,
    tag_id integer
);



COMMENT ON TABLE public.operation_tag IS 'Tag for operation';



CREATE SEQUENCE public.operation_tag_opt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.operation_tag_opt_id_seq OWNED BY public.operation_tag.opt_id;



CREATE TABLE public.parameter (
    pr_id text NOT NULL,
    pr_value text
);



COMMENT ON TABLE public.parameter IS 'parameter of the company';



CREATE TABLE public.parameter_extra (
    id integer NOT NULL,
    pe_code text,
    pe_label text,
    pe_value text
);



COMMENT ON TABLE public.parameter_extra IS 'Extra parameter for the folder';



COMMENT ON COLUMN public.parameter_extra.pe_code IS 'Code used in the Document:generate';



COMMENT ON COLUMN public.parameter_extra.pe_label IS 'Label description';



COMMENT ON COLUMN public.parameter_extra.pe_value IS 'Value which will replace the tag in Document:generate';



CREATE SEQUENCE public.parameter_extra_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.parameter_extra_id_seq OWNED BY public.parameter_extra.id;



CREATE TABLE public.parm_appearance (
    a_code text NOT NULL,
    a_value text NOT NULL
);



CREATE TABLE public.parm_code (
    p_code text NOT NULL,
    p_value text,
    p_comment text
);



COMMENT ON TABLE public.parm_code IS 'Parameter code and accountancy';



CREATE TABLE public.parm_money (
    pm_id integer DEFAULT nextval(('s_currency'::text)::regclass),
    pm_code character(3) NOT NULL,
    pm_rate numeric(20,4)
);



COMMENT ON TABLE public.parm_money IS 'Currency conversion';



CREATE TABLE public.parm_periode (
    p_id integer DEFAULT nextval(('s_periode'::text)::regclass) NOT NULL,
    p_start date NOT NULL,
    p_end date NOT NULL,
    p_exercice text DEFAULT to_char(now(), 'YYYY'::text) NOT NULL,
    p_closed boolean DEFAULT false,
    p_central boolean DEFAULT false,
    p_exercice_label text NOT NULL,
    CONSTRAINT parm_periode_check CHECK ((p_end >= p_start))
);



COMMENT ON TABLE public.parm_periode IS 'Periode definition';



COMMENT ON COLUMN public.parm_periode.p_start IS 'Start date of periode';



COMMENT ON COLUMN public.parm_periode.p_end IS 'End date of periode';



COMMENT ON COLUMN public.parm_periode.p_exercice IS 'Exercice';



COMMENT ON COLUMN public.parm_periode.p_closed IS 'is closed';



COMMENT ON COLUMN public.parm_periode.p_central IS 'is centralized (obsolete)';



COMMENT ON COLUMN public.parm_periode.p_exercice_label IS 'label of the exercice';



CREATE TABLE public.parm_poste (
    p_value public.account_type NOT NULL,
    p_type text NOT NULL
);



COMMENT ON TABLE public.parm_poste IS 'Contains data for finding is the type of the account (asset)';



CREATE SEQUENCE public.plan_analytique_pa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.plan_analytique (
    pa_id integer DEFAULT nextval('public.plan_analytique_pa_id_seq'::regclass) NOT NULL,
    pa_name text DEFAULT 'Sans Nom'::text NOT NULL,
    pa_description text
);



COMMENT ON TABLE public.plan_analytique IS 'Plan Analytique (max 5)';



CREATE SEQUENCE public.poste_analytique_po_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.poste_analytique (
    po_id integer DEFAULT nextval('public.poste_analytique_po_id_seq'::regclass) NOT NULL,
    po_name text NOT NULL,
    pa_id integer NOT NULL,
    po_amount numeric(20,4) DEFAULT 0.0 NOT NULL,
    po_description text,
    ga_id character varying(10),
    po_state integer DEFAULT 1 NOT NULL
);



COMMENT ON TABLE public.poste_analytique IS 'Poste Analytique';



COMMENT ON COLUMN public.poste_analytique.po_state IS 'Analytic Account state : 0 disabled 0 enabled ';



CREATE TABLE public.profile (
    p_name text NOT NULL,
    p_id integer NOT NULL,
    p_desc text,
    with_calc boolean DEFAULT true,
    with_direct_form boolean DEFAULT true,
    with_search_card integer
);



COMMENT ON TABLE public.profile IS 'Available profile ';



COMMENT ON COLUMN public.profile.p_name IS 'Name of the profile';



COMMENT ON COLUMN public.profile.p_desc IS 'description of the profile';



COMMENT ON COLUMN public.profile.with_calc IS 'show the calculator';



COMMENT ON COLUMN public.profile.with_direct_form IS 'show the direct form';



COMMENT ON COLUMN public.profile.with_search_card IS 'Display a icon for searching card : 1 display, 0 not displaid';



CREATE TABLE public.profile_menu (
    pm_id integer NOT NULL,
    me_code text,
    me_code_dep text,
    p_id integer,
    p_order integer,
    p_type_display text NOT NULL,
    pm_default integer,
    pm_id_dep bigint
);



COMMENT ON TABLE public.profile_menu IS 'Join  between the profile and the menu ';



COMMENT ON COLUMN public.profile_menu.me_code_dep IS 'menu code dependency';



COMMENT ON COLUMN public.profile_menu.p_id IS 'link to profile';



COMMENT ON COLUMN public.profile_menu.p_order IS 'order of displaying menu';



COMMENT ON COLUMN public.profile_menu.p_type_display IS 'M is a module
E is a menu
S is a select (for plugin)';



COMMENT ON COLUMN public.profile_menu.pm_default IS 'default menu';



COMMENT ON COLUMN public.profile_menu.pm_id_dep IS 'parent of this menu item';



CREATE SEQUENCE public.profile_menu_pm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.profile_menu_pm_id_seq OWNED BY public.profile_menu.pm_id;



CREATE TABLE public.profile_menu_type (
    pm_type text NOT NULL,
    pm_desc text
);



COMMENT ON TABLE public.profile_menu_type IS 'Type of menu';



CREATE TABLE public.profile_mobile (
    pmo_id integer NOT NULL,
    me_code text NOT NULL,
    pmo_order integer NOT NULL,
    p_id integer NOT NULL,
    pmo_default character(1) DEFAULT '1'::bpchar
);



COMMENT ON TABLE public.profile_mobile IS 'Menu for mobile device';



COMMENT ON COLUMN public.profile_mobile.pmo_id IS 'primary key';



COMMENT ON COLUMN public.profile_mobile.me_code IS 'Code of menu_ref to execute';



COMMENT ON COLUMN public.profile_mobile.pmo_order IS 'item order in menu';



COMMENT ON COLUMN public.profile_mobile.p_id IS 'Profile id ';



COMMENT ON COLUMN public.profile_mobile.pmo_default IS 'possible values are 1 , the default HTML header (javascript,CSS,...) is loaded ,  0  nothing is loaded from noalyss ';



CREATE SEQUENCE public.profile_mobile_pmo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.profile_mobile_pmo_id_seq OWNED BY public.profile_mobile.pmo_id;



CREATE SEQUENCE public.profile_p_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.profile_p_id_seq OWNED BY public.profile.p_id;



CREATE TABLE public.profile_sec_repository (
    ur_id bigint NOT NULL,
    p_id bigint,
    r_id bigint,
    ur_right character(1),
    CONSTRAINT user_sec_profile_ur_right_check CHECK ((ur_right = ANY (ARRAY['R'::bpchar, 'W'::bpchar])))
);



COMMENT ON TABLE public.profile_sec_repository IS 'Available profile for user';



COMMENT ON COLUMN public.profile_sec_repository.ur_id IS 'pk';



COMMENT ON COLUMN public.profile_sec_repository.p_id IS 'fk to profile';



COMMENT ON COLUMN public.profile_sec_repository.r_id IS 'fk to stock_repository';



COMMENT ON COLUMN public.profile_sec_repository.ur_right IS 'Type of right : R for readonly W for write';



CREATE SEQUENCE public.profile_sec_repository_ur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.profile_sec_repository_ur_id_seq OWNED BY public.profile_sec_repository.ur_id;



CREATE TABLE public.profile_user (
    user_name text NOT NULL,
    pu_id integer NOT NULL,
    p_id integer
);



COMMENT ON TABLE public.profile_user IS 'Contains the available profile for users';



COMMENT ON COLUMN public.profile_user.user_name IS 'fk to available_user : login';



COMMENT ON COLUMN public.profile_user.p_id IS 'fk to profile';



CREATE SEQUENCE public.profile_user_pu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.profile_user_pu_id_seq OWNED BY public.profile_user.pu_id;



CREATE TABLE public.quant_fin (
    qf_id bigint NOT NULL,
    qf_bank bigint,
    jr_id bigint,
    qf_other bigint,
    qf_amount numeric(20,4) DEFAULT 0,
    j_id bigint
);



COMMENT ON TABLE public.quant_fin IS 'Simple operation for financial';



CREATE SEQUENCE public.quant_fin_qf_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.quant_fin_qf_id_seq OWNED BY public.quant_fin.qf_id;



CREATE TABLE public.quant_purchase (
    qp_id integer DEFAULT nextval(('s_quantity'::text)::regclass) NOT NULL,
    qp_internal text,
    j_id integer NOT NULL,
    qp_fiche integer NOT NULL,
    qp_quantite numeric(20,4) NOT NULL,
    qp_price numeric(20,4),
    qp_vat numeric(20,4) DEFAULT 0.0,
    qp_vat_code integer,
    qp_nd_amount numeric(20,4) DEFAULT 0.0,
    qp_nd_tva numeric(20,4) DEFAULT 0.0,
    qp_nd_tva_recup numeric(20,4) DEFAULT 0.0,
    qp_supplier integer NOT NULL,
    qp_valid character(1) DEFAULT 'Y'::bpchar NOT NULL,
    qp_dep_priv numeric(20,4) DEFAULT 0.0,
    qp_vat_sided numeric(20,4) DEFAULT 0.0,
    qp_unit numeric(20,4) DEFAULT 0
);



COMMENT ON TABLE public.quant_purchase IS 'Supplemental info for purchase';



COMMENT ON COLUMN public.quant_purchase.qp_vat_sided IS 'amount of the VAT which avoid VAT, case of the VAT which add the same amount at the deb and cred';



CREATE TABLE public.quant_sold (
    qs_id integer DEFAULT nextval(('s_quantity'::text)::regclass) NOT NULL,
    qs_internal text,
    qs_fiche integer NOT NULL,
    qs_quantite numeric(20,4) NOT NULL,
    qs_price numeric(20,4),
    qs_vat numeric(20,4),
    qs_vat_code integer,
    qs_client integer NOT NULL,
    qs_valid character(1) DEFAULT 'Y'::bpchar NOT NULL,
    j_id integer NOT NULL,
    qs_vat_sided numeric(20,4) DEFAULT 0.0,
    qs_unit numeric(20,4) DEFAULT 0
);



COMMENT ON TABLE public.quant_sold IS 'Contains about invoice for customer';



CREATE SEQUENCE public.s_attr_def
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_cbc
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_central
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_central_order
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_centralized
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_currency
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_fdef
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_fiche
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_fiche_def_ref
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_form
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_formdef
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_grpt
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_idef
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_internal
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_invoice
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_isup
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jnt_fic_att_value
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_2
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_3
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_4
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_5
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_def
    START WITH 5
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_op
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_pj1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_pj2
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_pj3
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_pj4
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_pj5
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrn_rapt
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrnaction
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_jrnx
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_periode
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_quantity
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_stock_goods
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_tva
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_user_act
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.s_user_jrn
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_bud_hypothese_bh_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_10
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_2
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_20
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_21
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_22
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_3
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_4
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_5
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_6
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_7
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_8
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE SEQUENCE public.seq_doc_type_9
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.stock_change (
    c_id bigint NOT NULL,
    c_comment text,
    c_date date,
    tech_user text,
    r_id bigint,
    tech_date time without time zone DEFAULT now() NOT NULL
);



COMMENT ON TABLE public.stock_change IS 'Change of stock';



CREATE SEQUENCE public.stock_change_c_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.stock_change_c_id_seq OWNED BY public.stock_change.c_id;



CREATE TABLE public.stock_goods (
    sg_id integer DEFAULT nextval(('s_stock_goods'::text)::regclass) NOT NULL,
    j_id integer,
    f_id integer,
    sg_code text,
    sg_quantity numeric(8,4) DEFAULT 0,
    sg_type character(1) DEFAULT 'c'::bpchar NOT NULL,
    sg_date date,
    sg_tech_date date DEFAULT now(),
    sg_tech_user text,
    sg_comment character varying(80),
    sg_exercice character varying(4),
    r_id bigint,
    c_id bigint,
    CONSTRAINT stock_goods_sg_type CHECK (((sg_type = 'c'::bpchar) OR (sg_type = 'd'::bpchar)))
);



COMMENT ON TABLE public.stock_goods IS 'About the goods';



CREATE TABLE public.stock_repository (
    r_id bigint NOT NULL,
    r_name text,
    r_adress text,
    r_country text,
    r_city text,
    r_phone text
);



COMMENT ON TABLE public.stock_repository IS 'stock repository';



COMMENT ON COLUMN public.stock_repository.r_id IS 'pk';



COMMENT ON COLUMN public.stock_repository.r_name IS 'name of the stock';



COMMENT ON COLUMN public.stock_repository.r_adress IS 'adress of the stock';



COMMENT ON COLUMN public.stock_repository.r_country IS 'country of the stock';



COMMENT ON COLUMN public.stock_repository.r_city IS 'City of the stock';



COMMENT ON COLUMN public.stock_repository.r_phone IS 'Phone number';



CREATE SEQUENCE public.stock_repository_r_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.stock_repository_r_id_seq OWNED BY public.stock_repository.r_id;



CREATE TABLE public.tag_group (
    tg_id bigint NOT NULL,
    tg_name character varying NOT NULL
);



COMMENT ON TABLE public.tag_group IS 'Group of tags';



COMMENT ON COLUMN public.tag_group.tg_name IS 'Nom du groupe';



CREATE SEQUENCE public.tag_group_tg_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.tag_group_tg_id_seq OWNED BY public.tag_group.tg_id;



CREATE TABLE public.tags (
    t_id integer NOT NULL,
    t_tag text NOT NULL,
    t_description text,
    t_actif character(1) DEFAULT 'Y'::bpchar,
    t_color integer DEFAULT 1,
    CONSTRAINT tags_check CHECK ((t_actif = ANY (ARRAY['N'::bpchar, 'Y'::bpchar])))
);



COMMENT ON TABLE public.tags IS 'Tags name';



COMMENT ON COLUMN public.tags.t_actif IS 'Y if the tag is activate and can be used ';



CREATE SEQUENCE public.tags_t_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.tags_t_id_seq OWNED BY public.tags.t_id;



CREATE SEQUENCE public.tmp_pcmn_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.tmp_pcmn (
    pcm_val public.account_type NOT NULL,
    pcm_lib text,
    pcm_val_parent public.account_type DEFAULT 0,
    pcm_type text,
    id bigint DEFAULT nextval('public.tmp_pcmn_id_seq'::regclass) NOT NULL,
    pcm_direct_use character varying(1) DEFAULT 'Y'::character varying NOT NULL,
    CONSTRAINT pcm_direct_use_ck CHECK (((pcm_direct_use)::text = ANY (ARRAY[('Y'::character varying)::text, ('N'::character varying)::text])))
);



COMMENT ON TABLE public.tmp_pcmn IS 'Plan comptable minimum normalisé';



COMMENT ON COLUMN public.tmp_pcmn.id IS 'allow to identify the row, it is unique and not null (pseudo pk)';



COMMENT ON COLUMN public.tmp_pcmn.pcm_direct_use IS 'Value are N or Y , N cannot be used directly , not even through a card';



CREATE TABLE public.tmp_stockgood (
    s_id bigint NOT NULL,
    s_date timestamp without time zone DEFAULT now()
);



CREATE TABLE public.tmp_stockgood_detail (
    d_id bigint NOT NULL,
    s_id bigint,
    sg_code text,
    s_qin numeric(20,4),
    s_qout numeric(20,4),
    r_id bigint,
    f_id bigint
);



CREATE SEQUENCE public.tmp_stockgood_detail_d_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.tmp_stockgood_detail_d_id_seq OWNED BY public.tmp_stockgood_detail.d_id;



CREATE SEQUENCE public.tmp_stockgood_s_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.tmp_stockgood_s_id_seq OWNED BY public.tmp_stockgood.s_id;



CREATE SEQUENCE public.todo_list_tl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.todo_list (
    tl_id integer DEFAULT nextval('public.todo_list_tl_id_seq'::regclass) NOT NULL,
    tl_date date NOT NULL,
    tl_title text NOT NULL,
    tl_desc text,
    use_login text NOT NULL,
    is_public character(1) DEFAULT 'N'::bpchar NOT NULL,
    CONSTRAINT ck_is_public CHECK ((is_public = ANY (ARRAY['Y'::bpchar, 'N'::bpchar])))
);



COMMENT ON TABLE public.todo_list IS 'Todo list';



COMMENT ON COLUMN public.todo_list.is_public IS 'Flag for the public parameter';



CREATE TABLE public.todo_list_shared (
    id integer NOT NULL,
    todo_list_id integer NOT NULL,
    use_login text NOT NULL
);



COMMENT ON TABLE public.todo_list_shared IS 'Note of todo list shared with other users';



COMMENT ON COLUMN public.todo_list_shared.todo_list_id IS 'fk to todo_list';



COMMENT ON COLUMN public.todo_list_shared.use_login IS 'user login';



CREATE SEQUENCE public.todo_list_shared_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.todo_list_shared_id_seq OWNED BY public.todo_list_shared.id;



CREATE SEQUENCE public.uos_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



CREATE TABLE public.tool_uos (
    uos_value bigint DEFAULT nextval('public.uos_pk_seq'::regclass) NOT NULL
);



CREATE TABLE public.tva_rate (
    tva_id integer DEFAULT nextval('public.s_tva'::regclass) NOT NULL,
    tva_label text NOT NULL,
    tva_rate numeric(8,4) DEFAULT 0.0 NOT NULL,
    tva_comment text,
    tva_poste text,
    tva_both_side integer DEFAULT 0,
    tva_payment_sale character(1) DEFAULT 'O'::bpchar,
    tva_payment_purchase character(1) DEFAULT 'O'::bpchar,
    tva_code text NOT NULL,
    CONSTRAINT tva_code_number_check CHECK ((tva_code !~ '^([0-9]+)$'::text)),
    CONSTRAINT tva_rate_purchase_check CHECK ((tva_payment_purchase = ANY (ARRAY['O'::bpchar, 'P'::bpchar]))),
    CONSTRAINT tva_rate_sale_check CHECK ((tva_payment_sale = ANY (ARRAY['O'::bpchar, 'P'::bpchar])))
);



COMMENT ON TABLE public.tva_rate IS 'Rate of vat';



COMMENT ON COLUMN public.tva_rate.tva_label IS 'Label';



COMMENT ON COLUMN public.tva_rate.tva_rate IS 'Rate';



COMMENT ON COLUMN public.tva_rate.tva_comment IS 'Description of VAT';



COMMENT ON COLUMN public.tva_rate.tva_poste IS 'accounting';



COMMENT ON COLUMN public.tva_rate.tva_both_side IS 'If set to 1 , the amount VAT will be reversed (autoliquidation)';



COMMENT ON COLUMN public.tva_rate.tva_payment_sale IS 'Check if the VAT on Sale  must be declared when at the date of payment (P) or the date of operation (O)';



COMMENT ON COLUMN public.tva_rate.tva_payment_purchase IS 'Check if the VAT on Purchase must be declared when at the date of payment (P) or the date of operation (O)';



CREATE TABLE public.user_active_security (
    id integer NOT NULL,
    us_login text NOT NULL,
    us_ledger character varying(1) NOT NULL,
    us_action character varying(1) NOT NULL,
    CONSTRAINT user_active_security_action_check CHECK (((us_action)::text = ANY (ARRAY[('Y'::character varying)::text, ('N'::character varying)::text]))),
    CONSTRAINT user_active_security_ledger_check CHECK (((us_ledger)::text = ANY (ARRAY[('Y'::character varying)::text, ('N'::character varying)::text])))
);



COMMENT ON TABLE public.user_active_security IS 'Security for user';



COMMENT ON COLUMN public.user_active_security.us_login IS 'user''s login';



COMMENT ON COLUMN public.user_active_security.us_ledger IS 'Flag Security for ledger';



COMMENT ON COLUMN public.user_active_security.us_action IS 'Security for action';



CREATE SEQUENCE public.user_active_security_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.user_active_security_id_seq OWNED BY public.user_active_security.id;



CREATE TABLE public.user_filter (
    id bigint NOT NULL,
    login text,
    nb_jrn integer,
    date_start character varying(10),
    date_end character varying(10),
    description text,
    amount_min numeric(20,4),
    amount_max numeric(20,4),
    qcode text,
    accounting text,
    r_jrn text,
    date_paid_start character varying(10),
    date_paid_end character varying(10),
    ledger_type character varying(5),
    all_ledger integer,
    filter_name text NOT NULL,
    operation_filter text NOT NULL,
    uf_tag text,
    uf_tag_option integer,
    uf_currency_code integer,
    tva_id_search integer
);



COMMENT ON TABLE public.user_filter IS 'Filter for the search';



COMMENT ON COLUMN public.user_filter.operation_filter IS 'Status of the operation  : paid, unpaid or all operation';



COMMENT ON COLUMN public.user_filter.uf_tag IS 'Tag list';



COMMENT ON COLUMN public.user_filter.uf_tag_option IS '0 : all tags must be present, 1: at least one';



COMMENT ON COLUMN public.user_filter.uf_currency_code IS 'correspond to currency.id';



COMMENT ON COLUMN public.user_filter.tva_id_search IS 'VAT id ';



CREATE SEQUENCE public.user_filter_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.user_filter_id_seq OWNED BY public.user_filter.id;



CREATE TABLE public.user_local_pref (
    user_id text NOT NULL,
    parameter_type text NOT NULL,
    parameter_value text
);



COMMENT ON TABLE public.user_local_pref IS 'The user''s local parameter ';



COMMENT ON COLUMN public.user_local_pref.user_id IS 'user''s login ';



COMMENT ON COLUMN public.user_local_pref.parameter_type IS 'the type of parameter ';



COMMENT ON COLUMN public.user_local_pref.parameter_value IS 'the value of parameter ';



CREATE TABLE public.user_sec_act (
    ua_id integer DEFAULT nextval(('s_user_act'::text)::regclass) NOT NULL,
    ua_login text,
    ua_act_id integer
);



COMMENT ON TABLE public.user_sec_act IS 'Security on actions for user';



CREATE TABLE public.user_sec_action_profile (
    ua_id bigint NOT NULL,
    p_id bigint,
    p_granted bigint,
    ua_right character(1),
    CONSTRAINT user_sec_action_profile_ua_right_check CHECK ((ua_right = ANY (ARRAY['R'::bpchar, 'W'::bpchar, 'X'::bpchar, 'O'::bpchar])))
);



COMMENT ON TABLE public.user_sec_action_profile IS 'Available profile for user';



COMMENT ON COLUMN public.user_sec_action_profile.ua_id IS 'pk';



COMMENT ON COLUMN public.user_sec_action_profile.p_id IS 'fk to profile';



COMMENT ON COLUMN public.user_sec_action_profile.ua_right IS 'Type of right : R for readonly W for write';



CREATE SEQUENCE public.user_sec_action_profile_ua_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE public.user_sec_action_profile_ua_id_seq OWNED BY public.user_sec_action_profile.ua_id;



CREATE TABLE public.user_sec_jrn (
    uj_id integer DEFAULT nextval(('s_user_jrn'::text)::regclass) NOT NULL,
    uj_login text,
    uj_jrn_id integer,
    uj_priv text
);



COMMENT ON TABLE public.user_sec_jrn IS 'Security on ledger for users';



CREATE TABLE public.user_widget (
    uw_id integer NOT NULL,
    use_login text NOT NULL,
    dashboard_widget_id integer NOT NULL,
    uw_parameter text,
    uw_order integer
);



ALTER TABLE public.user_widget ALTER COLUMN uw_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.user_widget_uw_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE VIEW public.v_all_account_currency AS
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_fin q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON ((q1.jr_id = x.jr_id)))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id
UNION ALL
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_purchase q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste,
            j.jr_internal
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON (((q1.qp_internal = x.jr_internal) AND ((x.f_id = q1.qp_fiche) OR (x.f_id = q1.qp_supplier)))))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id
UNION ALL
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_sold q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste,
            j.jr_internal
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON (((q1.qs_internal = x.jr_internal) AND ((x.f_id = q1.qs_fiche) OR (x.f_id = q1.qs_client)))))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id;



CREATE VIEW public.v_all_card_currency AS
 SELECT sum(operation_currency.oc_amount) AS sum_oc_amount,
    sum(operation_currency.oc_vat_amount) AS sum_oc_vat_amount,
    jrnx.f_id,
    operation_currency.j_id
   FROM (public.operation_currency
     JOIN public.jrnx USING (j_id))
  GROUP BY jrnx.f_id, operation_currency.j_id;



CREATE VIEW public.v_all_menu AS
 SELECT pm.me_code,
    pm.pm_id,
    pm.me_code_dep,
    pm.p_order,
    pm.p_type_display,
    p.p_name,
    p.p_desc,
    mr.me_menu,
    mr.me_file,
    mr.me_url,
    mr.me_parameter,
    mr.me_javascript,
    mr.me_type,
    pm.p_id,
    mr.me_description
   FROM ((public.profile_menu pm
     JOIN public.profile p ON ((p.p_id = pm.p_id)))
     JOIN public.menu_ref mr USING (me_code))
  ORDER BY pm.p_order;



CREATE VIEW public.v_contact AS
 WITH contact_data AS (
         SELECT f.f_id,
            f.f_enable,
            f.fd_id
           FROM (public.fiche f
             JOIN public.fiche_def fd ON ((f.fd_id = fd.fd_id)))
          WHERE (fd.frd_id = 16)
        )
 SELECT cd.f_id,
    cd.f_enable,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 32) AND (fiche_detail.f_id = cd.f_id))) AS contact_fname,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 1) AND (fiche_detail.f_id = cd.f_id))) AS contact_name,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 23) AND (fiche_detail.f_id = cd.f_id))) AS contact_qcode,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 25) AND (fiche_detail.f_id = cd.f_id))) AS contact_company,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 27) AND (fiche_detail.f_id = cd.f_id))) AS contact_mobile,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 17) AND (fiche_detail.f_id = cd.f_id))) AS contact_phone,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 18) AND (fiche_detail.f_id = cd.f_id))) AS contact_email,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 26) AND (fiche_detail.f_id = cd.f_id))) AS contact_fax,
    cd.fd_id AS card_category
   FROM contact_data cd;



CREATE VIEW public.v_currency_last_value AS
 WITH recent_rate AS (
         SELECT currency_history.currency_id,
            max(currency_history.ch_from) AS rc_from
           FROM public.currency_history
          GROUP BY currency_history.currency_id
        )
 SELECT cr1.id AS currency_id,
    cr1.cr_name,
    cr1.cr_code_iso,
    ch1.id AS currency_history_id,
    ch1.ch_value,
    to_char((recent_rate.rc_from)::timestamp with time zone, 'DD.MM.YYYY'::text) AS str_from
   FROM ((public.currency cr1
     JOIN recent_rate ON ((recent_rate.currency_id = cr1.id)))
     JOIN public.currency_history ch1 ON (((recent_rate.currency_id = ch1.currency_id) AND (recent_rate.rc_from = ch1.ch_from))));



CREATE VIEW public.vw_fiche_attr AS
 SELECT a.f_id,
    a.fd_id,
    a.ad_value AS vw_name,
    k.ad_value AS vw_first_name,
    b.ad_value AS vw_sell,
    c.ad_value AS vw_buy,
    d.ad_value AS tva_code,
    tva_rate.tva_id,
    tva_rate.tva_rate,
    tva_rate.tva_label,
    e.ad_value AS vw_addr,
    f.ad_value AS vw_cp,
    j.ad_value AS quick_code,
    h.ad_value AS vw_description,
    i.ad_value AS tva_num,
    fiche_def.frd_id,
    l.ad_value AS accounting,
    a.f_enable
   FROM ((((((((((((( SELECT fiche.f_id,
            fiche.fd_id,
            fiche.f_enable,
            fiche_detail.ad_value
           FROM (public.fiche
             LEFT JOIN public.fiche_detail USING (f_id))
          WHERE (fiche_detail.ad_id = 1)) a
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 6)) b ON ((a.f_id = b.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 7)) c ON ((a.f_id = c.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 2)) d ON ((a.f_id = d.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) e ON ((a.f_id = e.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) f ON ((a.f_id = f.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) j ON ((a.f_id = j.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 9)) h ON ((a.f_id = h.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) i ON ((a.f_id = i.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 32)) k ON ((a.f_id = k.f_id)))
     LEFT JOIN public.tva_rate ON ((d.ad_value = (tva_rate.tva_id)::text)))
     JOIN public.fiche_def USING (fd_id))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) l ON ((a.f_id = l.f_id)));



COMMENT ON VIEW public.vw_fiche_attr IS 'Some attribute for all cards';



CREATE VIEW public.vw_fiche_name AS
 SELECT fiche_detail.f_id,
    fiche_detail.ad_value AS name
   FROM public.fiche_detail
  WHERE (fiche_detail.ad_id = 1);



CREATE VIEW public.v_detail_purchase AS
 WITH m AS (
         SELECT sum(quant_purchase_1.qp_price) AS htva,
            sum(quant_purchase_1.qp_vat) AS tot_vat,
            sum(quant_purchase_1.qp_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM ((public.quant_purchase quant_purchase_1
             JOIN public.jrnx jrnx_1 USING (j_id))
             JOIN public.jrn jrn_1 ON ((jrnx_1.j_grpt = jrn_1.jr_grpt_id)))
          GROUP BY jrn_1.jr_id
        ), other_tax AS (
         SELECT jrnx_1.j_grpt,
            sum(
                CASE
                    WHEN (jrnx_1.j_debit IS FALSE) THEN ((0)::numeric - jrnx_1.j_montant)
                    ELSE jrnx_1.j_montant
                END) AS other_tax_amount
           FROM (public.jrnx jrnx_1
             JOIN public.jrn_tax USING (j_id))
          GROUP BY jrnx_1.j_grpt
        )
 SELECT jrn.jr_id,
    jrn.jr_date,
    jrn.jr_date_paid,
    jrn.jr_ech,
    jrn.jr_tech_per,
    jrn.jr_comment,
    jrn.jr_pj_number,
    jrn.jr_internal,
    jrn.jr_def_id,
    jrnx.j_poste,
    jrnx.j_text,
    jrnx.j_qcode,
    jrn.jr_rapt,
    quant_purchase.qp_fiche AS item_card,
    a.name AS item_name,
    quant_purchase.qp_supplier,
    b.vw_name AS tiers_name,
    b.quick_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    tva_rate.tva_both_side,
    quant_purchase.qp_vat_sided AS vat_sided,
    quant_purchase.qp_vat_code AS vat_code,
    quant_purchase.qp_vat AS vat,
    quant_purchase.qp_price AS price,
    quant_purchase.qp_quantite AS quantity,
    (quant_purchase.qp_price / quant_purchase.qp_quantite) AS price_per_unit,
    quant_purchase.qp_nd_amount AS non_ded_amount,
    quant_purchase.qp_nd_tva AS non_ded_tva,
    quant_purchase.qp_nd_tva_recup AS non_ded_tva_recup,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    ot.other_tax_amount,
    oc.oc_amount,
    oc.oc_vat_amount,
    ( SELECT currency.cr_code_iso
           FROM public.currency
          WHERE (jrn.currency_id = currency.id)) AS cr_code_iso
   FROM ((((((((public.jrn
     JOIN public.jrnx ON ((jrn.jr_grpt_id = jrnx.j_grpt)))
     JOIN public.quant_purchase USING (j_id))
     JOIN public.vw_fiche_name a ON ((quant_purchase.qp_fiche = a.f_id)))
     JOIN public.vw_fiche_attr b ON ((quant_purchase.qp_supplier = b.f_id)))
     LEFT JOIN public.tva_rate ON ((quant_purchase.qp_vat_code = tva_rate.tva_id)))
     JOIN m ON ((m.jr_id = jrn.jr_id)))
     LEFT JOIN public.operation_currency oc ON ((oc.j_id = jrnx.j_id)))
     LEFT JOIN other_tax ot ON ((ot.j_grpt = jrn.jr_grpt_id)));



CREATE VIEW public.v_detail_sale AS
 WITH m AS (
         SELECT sum(quant_sold_1.qs_price) AS htva,
            sum(quant_sold_1.qs_vat) AS tot_vat,
            sum(quant_sold_1.qs_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM ((public.quant_sold quant_sold_1
             JOIN public.jrnx jrnx_1 USING (j_id))
             JOIN public.jrn jrn_1 ON ((jrnx_1.j_grpt = jrn_1.jr_grpt_id)))
          GROUP BY jrn_1.jr_id
        ), other_tax AS (
         SELECT jrnx_1.j_grpt,
            sum(
                CASE
                    WHEN (jrnx_1.j_debit IS TRUE) THEN ((0)::numeric - jrnx_1.j_montant)
                    ELSE jrnx_1.j_montant
                END) AS other_tax_amount
           FROM (public.jrnx jrnx_1
             JOIN public.jrn_tax USING (j_id))
          GROUP BY jrnx_1.j_grpt
        )
 SELECT jrn.jr_id,
    jrn.jr_date,
    jrn.jr_date_paid,
    jrn.jr_ech,
    jrn.jr_tech_per,
    jrn.jr_comment,
    jrn.jr_pj_number,
    jrn.jr_internal,
    jrn.jr_def_id,
    jrnx.j_poste,
    jrnx.j_text,
    jrnx.j_qcode,
    jrn.jr_rapt,
    quant_sold.qs_fiche AS item_card,
    a.name AS item_name,
    quant_sold.qs_client,
    b.vw_name AS tiers_name,
    b.quick_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    tva_rate.tva_both_side,
    quant_sold.qs_vat_sided AS vat_sided,
    quant_sold.qs_vat_code AS vat_code,
    quant_sold.qs_vat AS vat,
    quant_sold.qs_price AS price,
    quant_sold.qs_quantite AS quantity,
    (quant_sold.qs_price / quant_sold.qs_quantite) AS price_per_unit,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    ot.other_tax_amount,
    oc.oc_amount,
    oc.oc_vat_amount,
    ( SELECT currency.cr_code_iso
           FROM public.currency
          WHERE (jrn.currency_id = currency.id)) AS cr_code_iso
   FROM ((((((((public.jrn
     JOIN public.jrnx ON ((jrn.jr_grpt_id = jrnx.j_grpt)))
     JOIN public.quant_sold USING (j_id))
     JOIN public.vw_fiche_name a ON ((quant_sold.qs_fiche = a.f_id)))
     JOIN public.vw_fiche_attr b ON ((quant_sold.qs_client = b.f_id)))
     LEFT JOIN public.tva_rate ON ((quant_sold.qs_vat_code = tva_rate.tva_id)))
     JOIN m ON ((m.jr_id = jrn.jr_id)))
     LEFT JOIN public.operation_currency oc ON ((oc.j_id = jrnx.j_id)))
     LEFT JOIN other_tax ot ON ((ot.j_grpt = jrn.jr_grpt_id)));



CREATE VIEW public.v_menu_dependency AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue,
            p.p_id
           FROM ((public.profile_menu pm
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.pm_id,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (v2.pm_id IS NOT NULL) THEN v2.pm_id
            WHEN (v3.pm_id IS NOT NULL) THEN v3.pm_id
            ELSE NULL::integer
        END AS higher_dep,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript,
    v1.p_id,
    v2.p_id AS v2pid,
    v3.p_id AS v3pid
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE ((COALESCE(v2.p_id, v1.p_id) = v1.p_id) AND (COALESCE(v3.p_id, v1.p_id) = v1.p_id) AND (v1.p_type_display <> 'P'::text))
  ORDER BY v1.pm_id;



CREATE VIEW public.v_menu_description AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            pm.pm_id_dep,
            pm.p_id,
            mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pu.user_name,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue
           FROM (((public.profile_menu pm
             JOIN public.profile_user pu ON ((pu.p_id = pm.p_id)))
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    v1.user_name,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript,
    v1.pm_id,
    v1.pm_id_dep,
    v1.p_id
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE ((v1.p_type_display <> 'P'::text) AND ((COALESCE(v1.me_file, ''::text) <> ''::text) OR (COALESCE(v1.me_javascript, ''::text) <> ''::text)));



COMMENT ON VIEW public.v_menu_description IS 'Description des menus';



CREATE VIEW public.v_menu_description_favori AS
 WITH t_menu AS (
         SELECT mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pu.user_name,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue
           FROM (((public.profile_menu pm
             JOIN public.profile_user pu ON ((pu.p_id = pm.p_id)))
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    v1.user_name,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE (v1.p_type_display <> 'P'::text);



CREATE VIEW public.v_menu_profile AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            pm.pm_id_dep,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pm.p_id
           FROM (public.profile_menu pm
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v3.p_type_display,
    COALESCE(v3.pm_id, 0) AS pm_id_v3,
    COALESCE(v2.pm_id, 0) AS pm_id_v2,
    v1.pm_id AS pm_id_v1,
    v1.p_id
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.pm_id_dep = v2.pm_id)))
     LEFT JOIN t_menu v3 ON ((v2.pm_id_dep = v3.pm_id)))
  WHERE (v1.p_type_display <> 'P'::text);



COMMENT ON VIEW public.v_menu_profile IS 'Give the profile and the menu + dependencies';



CREATE VIEW public.v_quant_detail AS
 WITH quant AS (
         SELECT quant_purchase.j_id,
            quant_purchase.qp_fiche AS fiche_id,
            quant_purchase.qp_supplier AS tiers,
            quant_purchase.qp_vat AS vat_amount,
            quant_purchase.qp_price AS price,
            quant_purchase.qp_vat_code AS vat_code,
            quant_purchase.qp_dep_priv AS dep_priv,
            quant_purchase.qp_nd_tva AS nd_tva,
            quant_purchase.qp_nd_tva_recup AS nd_tva_recup,
            quant_purchase.qp_nd_amount AS nd_amount,
            quant_purchase.qp_vat_sided AS vat_sided
           FROM public.quant_purchase
        UNION ALL
         SELECT quant_sold.j_id,
            quant_sold.qs_fiche,
            quant_sold.qs_client,
            quant_sold.qs_vat,
            quant_sold.qs_price,
            quant_sold.qs_vat_code,
            0,
            0,
            0,
            0,
            quant_sold.qs_vat_sided
           FROM public.quant_sold
        ), sum_jrn AS (
         SELECT jrn2.jr_id,
            quant2.tiers,
            sum(quant2.price) AS price,
            quant2.vat_code,
            sum(quant2.vat_amount) AS vat_amount,
            sum(quant2.dep_priv) AS dep_priv,
            sum(quant2.nd_tva) AS nd_tva,
            sum(quant2.nd_tva_recup) AS nd_tva_recup,
            sum(quant2.nd_amount) AS nd_amount,
            sum(quant2.vat_sided) AS vat_sided
           FROM ((public.jrn jrn2
             JOIN public.jrnx ON ((jrnx.j_grpt = jrn2.jr_grpt_id)))
             JOIN quant quant2 USING (j_id))
          GROUP BY quant2.tiers, jrn2.jr_id, quant2.vat_code
        )
 SELECT jrn.jr_id,
    sum_jrn.tiers,
    sum_jrn.price,
    sum_jrn.vat_code,
    sum_jrn.vat_amount,
    sum_jrn.dep_priv,
    sum_jrn.nd_tva,
    sum_jrn.nd_tva_recup,
    sum_jrn.nd_amount,
    sum_jrn.vat_sided,
    jrn_def.jrn_def_name,
    jrn_def.jrn_def_type,
    vw_fiche_name.name,
    jrn.jr_comment,
    jrn.jr_montant,
    tva_rate.tva_label
   FROM ((((public.jrn
     JOIN sum_jrn ON ((sum_jrn.jr_id = jrn.jr_id)))
     LEFT JOIN public.vw_fiche_name ON ((sum_jrn.tiers = vw_fiche_name.f_id)))
     JOIN public.jrn_def ON ((jrn_def.jrn_def_id = jrn.jr_def_id)))
     JOIN public.tva_rate ON ((tva_rate.tva_id = sum_jrn.vat_code)));



CREATE VIEW public.v_tva_rate AS
 SELECT tva_rate.tva_id,
    tva_rate.tva_rate,
    tva_rate.tva_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    split_part(tva_rate.tva_poste, ','::text, 1) AS tva_purchase,
    split_part(tva_rate.tva_poste, ','::text, 2) AS tva_sale,
    tva_rate.tva_both_side,
    tva_rate.tva_payment_purchase,
    tva_rate.tva_payment_sale
   FROM public.tva_rate;



CREATE TABLE public.version (
    val integer NOT NULL,
    v_description text,
    v_date timestamp without time zone DEFAULT now()
);



COMMENT ON TABLE public.version IS 'DB version';



CREATE VIEW public.vw_client AS
 SELECT fiche.f_id,
    a1.ad_value AS name,
    a.ad_value AS quick_code,
    b.ad_value AS tva_num,
    c.ad_value AS poste_comptable,
    d.ad_value AS rue,
    e.ad_value AS code_postal,
    f.ad_value AS pays,
    g.ad_value AS telephone,
    h.ad_value AS email
   FROM (((((((((((public.fiche
     JOIN public.fiche_def USING (fd_id))
     JOIN public.fiche_def_ref USING (frd_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 1)) a1 USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) b USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) c USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) d USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) e USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 16)) f USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 17)) g USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 18)) h USING (f_id))
  WHERE (fiche_def_ref.frd_id = 9);



CREATE VIEW public.vw_fiche_def AS
 SELECT jnt_fic_attr.fd_id,
    jnt_fic_attr.ad_id,
    attr_def.ad_text,
    fiche_def.fd_class_base,
    fiche_def.fd_label,
    fiche_def.fd_create_account,
    fiche_def.frd_id
   FROM ((public.fiche_def
     JOIN public.jnt_fic_attr USING (fd_id))
     JOIN public.attr_def ON ((attr_def.ad_id = jnt_fic_attr.ad_id)));



COMMENT ON VIEW public.vw_fiche_def IS 'all the attributs for	card family';



CREATE VIEW public.vw_fiche_min AS
 SELECT attr_min.frd_id,
    attr_min.ad_id,
    attr_def.ad_text,
    fiche_def_ref.frd_text,
    fiche_def_ref.frd_class_base
   FROM ((public.attr_min
     JOIN public.attr_def USING (ad_id))
     JOIN public.fiche_def_ref USING (frd_id));



CREATE VIEW public.vw_poste_qcode AS
 SELECT c.f_id,
    a.ad_value AS j_poste,
    b.ad_value AS j_qcode
   FROM ((public.fiche c
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) b USING (f_id));



CREATE VIEW public.vw_supplier AS
 SELECT fiche.f_id,
    a1.ad_value AS name,
    a.ad_value AS quick_code,
    b.ad_value AS tva_num,
    c.ad_value AS poste_comptable,
    d.ad_value AS rue,
    e.ad_value AS code_postal,
    f.ad_value AS pays,
    g.ad_value AS telephone,
    h.ad_value AS email
   FROM (((((((((((public.fiche
     JOIN public.fiche_def USING (fd_id))
     JOIN public.fiche_def_ref USING (frd_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 1)) a1 USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) b USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) c USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) d USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) e USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 16)) f USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 17)) g USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 18)) h USING (f_id))
  WHERE (fiche_def_ref.frd_id = 8);



CREATE TABLE public.widget_dashboard (
    wd_id integer NOT NULL,
    wd_code text NOT NULL,
    wd_name text NOT NULL,
    wd_description text NOT NULL,
    wd_parameter integer DEFAULT 0 NOT NULL
);



COMMENT ON COLUMN public.widget_dashboard.wd_id IS 'PK';



COMMENT ON COLUMN public.widget_dashboard.wd_code IS 'Code';



COMMENT ON COLUMN public.widget_dashboard.wd_name IS 'Name';



COMMENT ON COLUMN public.widget_dashboard.wd_description IS 'Description';



COMMENT ON COLUMN public.widget_dashboard.wd_parameter IS 'presence of  there is a parameter';



ALTER TABLE public.widget_dashboard ALTER COLUMN wd_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.widget_dashboard_wd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);



CREATE TABLE tva_belge.assujetti (
    a_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    xml_oid oid,
    periodicity character(1) NOT NULL,
    tva_name text,
    num_tva text,
    adress text,
    country text,
    date_decl date DEFAULT now(),
    periode_dec integer,
    exercice text
);



CREATE SEQUENCE tva_belge.assujetti_a_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.assujetti_a_id_seq OWNED BY tva_belge.assujetti.a_id;



CREATE TABLE tva_belge.assujetti_chld (
    ac_id integer NOT NULL,
    a_id bigint,
    ac_tvanum text NOT NULL,
    ac_amount numeric(20,4) NOT NULL,
    ac_vat numeric(20,4) NOT NULL,
    ac_qcode text NOT NULL,
    ac_name text NOT NULL,
    ac_periode text,
    exercice text
);



CREATE SEQUENCE tva_belge.assujetti_chld_ac_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.assujetti_chld_ac_id_seq OWNED BY tva_belge.assujetti_chld.ac_id;



CREATE TABLE tva_belge.declarant (
    d_id bigint NOT NULL,
    d_name text NOT NULL,
    d_street text NOT NULL,
    d_postcode text NOT NULL,
    d_city text NOT NULL,
    d_email text NOT NULL,
    d_phone text NOT NULL,
    d_vat_number text NOT NULL,
    d_countrycode character(2) NOT NULL
);



CREATE SEQUENCE tva_belge.declarant_d_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.declarant_d_id_seq OWNED BY tva_belge.declarant.d_id;



CREATE TABLE tva_belge.declaration_amount (
    da_id integer NOT NULL,
    d00 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d01 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d02 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d03 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d44 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d45 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d46 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d47 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d48 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d49 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d81 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d82 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d83 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d84 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d85 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d86 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d87 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d88 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d54 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d55 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d56 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d57 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d61 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d63 numeric(20,4) DEFAULT 0.0 NOT NULL,
    dxx numeric(20,4) DEFAULT 0.0 NOT NULL,
    d59 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d62 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d64 numeric(20,4) DEFAULT 0.0 NOT NULL,
    dyy numeric(20,4) DEFAULT 0.0 NOT NULL,
    d71 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d72 numeric(20,4) DEFAULT 0.0 NOT NULL,
    d91 numeric(20,4) DEFAULT 0.0 NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    xml_oid oid,
    periodicity character(1) NOT NULL,
    tva_name text,
    num_tva text,
    adress text,
    country text,
    date_decl date DEFAULT now(),
    periode_dec integer,
    exercice text,
    form_id integer,
    ask_restitution smallint DEFAULT 0,
    client_listing smallint DEFAULT 0
);



CREATE SEQUENCE tva_belge.declaration_amount_da_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.declaration_amount_da_id_seq OWNED BY tva_belge.declaration_amount.da_id;



CREATE TABLE tva_belge.form (
    id integer NOT NULL,
    f_name text NOT NULL,
    f_language character(2)
);



CREATE TABLE tva_belge.form_detail (
    id integer NOT NULL,
    fd_code text NOT NULL,
    fd_label text NOT NULL,
    fd_type text NOT NULL,
    fd_tva_code text,
    form_id integer NOT NULL,
    fd_order integer NOT NULL
);



CREATE SEQUENCE tva_belge.form_detail_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.form_detail_id_seq OWNED BY tva_belge.form_detail.id;



CREATE SEQUENCE tva_belge.form_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.form_id_seq OWNED BY tva_belge.form.id;



CREATE TABLE tva_belge.intracomm (
    i_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    xml_oid oid,
    periodicity character(1) NOT NULL,
    tva_name text,
    num_tva text,
    adress text,
    country text,
    date_decl date DEFAULT now(),
    periode_dec integer,
    exercice text
);



CREATE TABLE tva_belge.intracomm_chld (
    ic_id integer NOT NULL,
    i_id bigint,
    ic_tvanum text NOT NULL,
    ic_amount numeric(20,4) NOT NULL,
    ic_code character varying(1) NOT NULL,
    ic_periode character varying(6) NOT NULL,
    ic_qcode text NOT NULL,
    ic_name text NOT NULL
);



CREATE SEQUENCE tva_belge.intracomm_chld_ic_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.intracomm_chld_ic_id_seq OWNED BY tva_belge.intracomm_chld.ic_id;



CREATE SEQUENCE tva_belge.intracomm_i_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.intracomm_i_id_seq OWNED BY tva_belge.intracomm.i_id;



CREATE TABLE tva_belge.parameter (
    pcode text NOT NULL
);



CREATE TABLE tva_belge.parameter_chld (
    pi_id bigint NOT NULL,
    pcode text,
    tva_id bigint,
    pcm_val public.account_type
);



COMMENT ON TABLE tva_belge.parameter_chld IS 'Child table for parameters (TVA Plugin)';



COMMENT ON COLUMN tva_belge.parameter_chld.pi_id IS 'PK';



COMMENT ON COLUMN tva_belge.parameter_chld.pcode IS 'FK to parameter';



COMMENT ON COLUMN tva_belge.parameter_chld.tva_id IS 'FK to public.tva_rate';



COMMENT ON COLUMN tva_belge.parameter_chld.pcm_val IS 'FK to tmp_pcmn';



CREATE SEQUENCE tva_belge.parameter_chld_pi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.parameter_chld_pi_id_seq OWNED BY tva_belge.parameter_chld.pi_id;



CREATE TABLE tva_belge.representative (
    rp_id bigint NOT NULL,
    rp_issued text,
    rp_type text,
    rp_name text,
    rp_street text,
    rp_postcode text,
    rp_city text,
    rp_email text,
    rp_phone text,
    rp_countrycode character(2) DEFAULT NULL::bpchar
);



CREATE SEQUENCE tva_belge.representative_rp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.representative_rp_id_seq OWNED BY tva_belge.representative.rp_id;



CREATE TABLE tva_belge.version (
    id integer NOT NULL,
    vdate timestamp without time zone DEFAULT now(),
    vdesc text
);



CREATE SEQUENCE tva_belge.version_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE tva_belge.version_id_seq OWNED BY tva_belge.version.id;



ALTER TABLE ONLY public.acc_other_tax ALTER COLUMN ac_id SET DEFAULT nextval('public.acc_other_tax_ac_id_seq'::regclass);



ALTER TABLE ONLY public.action_detail ALTER COLUMN ad_id SET DEFAULT nextval('public.action_detail_ad_id_seq'::regclass);



ALTER TABLE ONLY public.action_gestion_comment ALTER COLUMN agc_id SET DEFAULT nextval('public.action_gestion_comment_agc_id_seq'::regclass);



ALTER TABLE ONLY public.action_gestion_operation ALTER COLUMN ago_id SET DEFAULT nextval('public.action_gestion_operation_ago_id_seq'::regclass);



ALTER TABLE ONLY public.action_gestion_related ALTER COLUMN aga_id SET DEFAULT nextval('public.action_gestion_related_aga_id_seq'::regclass);



ALTER TABLE ONLY public.action_person ALTER COLUMN ap_id SET DEFAULT nextval('public.action_person_ap_id_seq'::regclass);



ALTER TABLE ONLY public.action_person_option ALTER COLUMN ap_id SET DEFAULT nextval('public.action_person_option_ap_id_seq'::regclass);



ALTER TABLE ONLY public.action_tags ALTER COLUMN at_id SET DEFAULT nextval('public.action_tags_at_id_seq'::regclass);



ALTER TABLE ONLY public.bookmark ALTER COLUMN b_id SET DEFAULT nextval('public.bookmark_b_id_seq'::regclass);



ALTER TABLE ONLY public.contact_option_ref ALTER COLUMN cor_id SET DEFAULT nextval('public.contact_option_ref_cor_id_seq'::regclass);



ALTER TABLE ONLY public.currency ALTER COLUMN id SET DEFAULT nextval('public.currency_id_seq'::regclass);



ALTER TABLE ONLY public.currency_history ALTER COLUMN id SET DEFAULT nextval('public.currency_history_id_seq'::regclass);



ALTER TABLE ONLY public.del_action ALTER COLUMN del_id SET DEFAULT nextval('public.del_action_del_id_seq'::regclass);



ALTER TABLE ONLY public.del_jrn ALTER COLUMN dj_id SET DEFAULT nextval('public.del_jrn_dj_id_seq'::regclass);



ALTER TABLE ONLY public.del_jrnx ALTER COLUMN djx_id SET DEFAULT nextval('public.del_jrnx_djx_id_seq'::regclass);



ALTER TABLE ONLY public.document_option ALTER COLUMN do_id SET DEFAULT nextval('public.document_option_do_id_seq'::regclass);



ALTER TABLE ONLY public.extension ALTER COLUMN ex_id SET DEFAULT nextval('public.extension_ex_id_seq'::regclass);



ALTER TABLE ONLY public.forecast ALTER COLUMN f_id SET DEFAULT nextval('public.forecast_f_id_seq'::regclass);



ALTER TABLE ONLY public.forecast_category ALTER COLUMN fc_id SET DEFAULT nextval('public.forecast_cat_fc_id_seq'::regclass);



ALTER TABLE ONLY public.forecast_item ALTER COLUMN fi_id SET DEFAULT nextval('public.forecast_item_fi_id_seq'::regclass);



ALTER TABLE ONLY public.jnt_document_option_contact ALTER COLUMN jdoc_id SET DEFAULT nextval('public.jnt_document_option_contact_jdoc_id_seq'::regclass);



ALTER TABLE ONLY public.jnt_letter ALTER COLUMN jl_id SET DEFAULT nextval('public.jnt_letter_jl_id_seq'::regclass);



ALTER TABLE ONLY public.jnt_tag_group_tag ALTER COLUMN jt_id SET DEFAULT nextval('public.jnt_tag_group_tag_jt_id_seq'::regclass);



ALTER TABLE ONLY public.jrn_info ALTER COLUMN ji_id SET DEFAULT nextval('public.jrn_info_ji_id_seq'::regclass);



ALTER TABLE ONLY public.jrn_note ALTER COLUMN n_id SET DEFAULT nextval('public.jrn_note_n_id_seq'::regclass);



ALTER TABLE ONLY public.key_distribution ALTER COLUMN kd_id SET DEFAULT nextval('public.key_distribution_kd_id_seq'::regclass);



ALTER TABLE ONLY public.key_distribution_activity ALTER COLUMN ka_id SET DEFAULT nextval('public.key_distribution_activity_ka_id_seq'::regclass);



ALTER TABLE ONLY public.key_distribution_detail ALTER COLUMN ke_id SET DEFAULT nextval('public.key_distribution_detail_ke_id_seq'::regclass);



ALTER TABLE ONLY public.key_distribution_ledger ALTER COLUMN kl_id SET DEFAULT nextval('public.key_distribution_ledger_kl_id_seq'::regclass);



ALTER TABLE ONLY public.letter_cred ALTER COLUMN lc_id SET DEFAULT nextval('public.letter_cred_lc_id_seq'::regclass);



ALTER TABLE ONLY public.letter_deb ALTER COLUMN ld_id SET DEFAULT nextval('public.letter_deb_ld_id_seq'::regclass);



ALTER TABLE ONLY public.link_action_type ALTER COLUMN l_id SET DEFAULT nextval('public.link_action_type_l_id_seq'::regclass);



ALTER TABLE ONLY public.menu_default ALTER COLUMN md_id SET DEFAULT nextval('public.menu_default_md_id_seq'::regclass);



ALTER TABLE ONLY public.operation_currency ALTER COLUMN id SET DEFAULT nextval('public.operation_currency_id_seq'::regclass);



ALTER TABLE ONLY public.operation_tag ALTER COLUMN opt_id SET DEFAULT nextval('public.operation_tag_opt_id_seq'::regclass);



ALTER TABLE ONLY public.parameter_extra ALTER COLUMN id SET DEFAULT nextval('public.parameter_extra_id_seq'::regclass);



ALTER TABLE ONLY public.payment_method ALTER COLUMN mp_id SET DEFAULT nextval('public.mod_payment_mp_id_seq'::regclass);



ALTER TABLE ONLY public.profile ALTER COLUMN p_id SET DEFAULT nextval('public.profile_p_id_seq'::regclass);



ALTER TABLE ONLY public.profile_menu ALTER COLUMN pm_id SET DEFAULT nextval('public.profile_menu_pm_id_seq'::regclass);



ALTER TABLE ONLY public.profile_mobile ALTER COLUMN pmo_id SET DEFAULT nextval('public.profile_mobile_pmo_id_seq'::regclass);



ALTER TABLE ONLY public.profile_sec_repository ALTER COLUMN ur_id SET DEFAULT nextval('public.profile_sec_repository_ur_id_seq'::regclass);



ALTER TABLE ONLY public.profile_user ALTER COLUMN pu_id SET DEFAULT nextval('public.profile_user_pu_id_seq'::regclass);



ALTER TABLE ONLY public.quant_fin ALTER COLUMN qf_id SET DEFAULT nextval('public.quant_fin_qf_id_seq'::regclass);



ALTER TABLE ONLY public.stock_change ALTER COLUMN c_id SET DEFAULT nextval('public.stock_change_c_id_seq'::regclass);



ALTER TABLE ONLY public.stock_repository ALTER COLUMN r_id SET DEFAULT nextval('public.stock_repository_r_id_seq'::regclass);



ALTER TABLE ONLY public.tag_group ALTER COLUMN tg_id SET DEFAULT nextval('public.tag_group_tg_id_seq'::regclass);



ALTER TABLE ONLY public.tags ALTER COLUMN t_id SET DEFAULT nextval('public.tags_t_id_seq'::regclass);



ALTER TABLE ONLY public.tmp_stockgood ALTER COLUMN s_id SET DEFAULT nextval('public.tmp_stockgood_s_id_seq'::regclass);



ALTER TABLE ONLY public.tmp_stockgood_detail ALTER COLUMN d_id SET DEFAULT nextval('public.tmp_stockgood_detail_d_id_seq'::regclass);



ALTER TABLE ONLY public.todo_list_shared ALTER COLUMN id SET DEFAULT nextval('public.todo_list_shared_id_seq'::regclass);



ALTER TABLE ONLY public.user_active_security ALTER COLUMN id SET DEFAULT nextval('public.user_active_security_id_seq'::regclass);



ALTER TABLE ONLY public.user_filter ALTER COLUMN id SET DEFAULT nextval('public.user_filter_id_seq'::regclass);



ALTER TABLE ONLY public.user_sec_action_profile ALTER COLUMN ua_id SET DEFAULT nextval('public.user_sec_action_profile_ua_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.assujetti ALTER COLUMN a_id SET DEFAULT nextval('tva_belge.assujetti_a_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.assujetti_chld ALTER COLUMN ac_id SET DEFAULT nextval('tva_belge.assujetti_chld_ac_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.declarant ALTER COLUMN d_id SET DEFAULT nextval('tva_belge.declarant_d_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.declaration_amount ALTER COLUMN da_id SET DEFAULT nextval('tva_belge.declaration_amount_da_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.form ALTER COLUMN id SET DEFAULT nextval('tva_belge.form_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.form_detail ALTER COLUMN id SET DEFAULT nextval('tva_belge.form_detail_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.intracomm ALTER COLUMN i_id SET DEFAULT nextval('tva_belge.intracomm_i_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.intracomm_chld ALTER COLUMN ic_id SET DEFAULT nextval('tva_belge.intracomm_chld_ic_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.parameter_chld ALTER COLUMN pi_id SET DEFAULT nextval('tva_belge.parameter_chld_pi_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.representative ALTER COLUMN rp_id SET DEFAULT nextval('tva_belge.representative_rp_id_seq'::regclass);



ALTER TABLE ONLY tva_belge.version ALTER COLUMN id SET DEFAULT nextval('tva_belge.version_id_seq'::regclass);



ALTER TABLE ONLY public.acc_other_tax
    ADD CONSTRAINT acc_other_tax_pk PRIMARY KEY (ac_id);
