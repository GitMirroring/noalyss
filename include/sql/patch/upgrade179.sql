begin;
CREATE OR REPLACE FUNCTION comptaproc.account_compute_alpha(p_class text, p_name text)
    RETURNS account_type
AS $function$
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
$function$
LANGUAGE plpgsql;



CREATE OR REPLACE FUNCTION comptaproc.account_compute(p_f_id integer)
    RETURNS account_type
AS $function$
declare
    class_base fiche_def.fd_class_base%type;
    maxcode numeric;
    sResult account_type;
    bAlphanum bool;
    sName text;
begin
    select fd_class_base into class_base
    from
        fiche_def join fiche using (fd_id)
    where
            f_id=p_f_id;

    bAlphanum := account_alphanum();
    if bAlphanum = false  then
        select count (pcm_val) into maxcode from tmp_pcmn where pcm_val_parent = class_base;
        if maxcode = 0	then
            maxcode:=class_base::numeric;
        else
            select max (pcm_val) into maxcode from tmp_pcmn where pcm_val_parent = class_base;
            maxcode:=maxcode::numeric;
        end if;
        if maxcode::text = class_base then
            maxcode:=class_base::numeric*1000;
        end if;
        maxcode:=maxcode+1;

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
LANGUAGE plpgsql ;

insert into version (val,v_description) values (180,'Shorten Aphanumeric account');
commit;
