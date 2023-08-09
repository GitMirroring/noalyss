begin;
CREATE OR REPLACE FUNCTION comptaproc.account_compute(p_f_id integer)
    RETURNS account_type
AS $function$
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
$function$
LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION comptaproc.account_add(p_id account_type, p_name character varying)
    RETURNS text
AS $function$
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
$function$
LANGUAGE plpgsql;


insert into version (val,v_description) values (189,'Compute properly accounting');
commit;