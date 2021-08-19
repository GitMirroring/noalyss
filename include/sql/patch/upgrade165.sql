-- noinspection SqlNoDataSourceInspectionForFile

begin;

CREATE OR REPLACE FUNCTION comptaproc.format_quickcode(p_qcode text)
    RETURNS text
AS $function$
declare
    tText text;
BEGIN
    tText := lower(trim(p_qcode));
    tText := replace(tText,' ','');
    tText:= translate(tText,E' $€µ£%+/\\!(){}(),;&|"#''^<>*','');
    tText := translate(tText,E'éèêëàâäïîüûùöôç','eeeeaaaiiuuuooc');

    return upper(tText);
END;
$function$
LANGUAGE plpgsql;

COMMENT ON FUNCTION comptaproc.format_quickcode(text) IS 'Put in upper case and remove invalid char';

CREATE OR REPLACE FUNCTION comptaproc.insert_quick_code(nf_id integer, tav_text text)
    RETURNS integer
AS $function$
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
$function$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION comptaproc.update_quick_code(njft_id integer, tav_text text)
    RETURNS integer
AS $function$
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
$function$
    LANGUAGE plpgsql;

COMMENT ON FUNCTION comptaproc.update_quick_code(int4,text) IS 'update the qcode + related columns in other cards';
CREATE OR REPLACE FUNCTION comptaproc.fiche_detail_check()
 RETURNS trigger
AS $function$
	BEGIN
		if new.ad_id = 23 and coalesce (new.ad_value,'') = '' then 
			raise exception  'QUICKCODE can not be empty';
		end if;
	if new.ad_id = 1 and coalesce (new.ad_value,'') = '' then 
			raise exception  'NAME can not be empty';
		end if;
	return new;
	END;

$function$
LANGUAGE plpgsql;

create trigger fiche_detail_check_trg before update or insert 
    on
    public.fiche_detail for each row execute procedure comptaproc.fiche_detail_check() ;


insert into version (val,v_description) values (166,'Fix bug for card with empty name or quickcode');
commit ;
