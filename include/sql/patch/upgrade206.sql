begin;

CREATE OR REPLACE FUNCTION guess_country_code()
  RETURNS void
 AS
$$
declare
 country_code char(2);
 x record;
begin
    for x in
        select 
            f1.jft_id
            ,f1.f_id
            ,( select f2.ad_value from fiche_detail f2 where f2.f_id=f1.f_id and ad_id= 13) as tva_num
            ,( select f3.ad_value from fiche_detail f3 where f3.f_id=f1.f_id and ad_id= 16) as country
        from 
            fiche_detail  f1
        where 
            f1.ad_id=57 
            and coalesce(f1.ad_value,'')=''
    loop
        country_code='';
        case 
            when  substr(x.tva_num,1,2) = 'FR' or upper(x.country)='FRANCE'  then   country_code='FR';
            when  substr(x.tva_num,1,2) = 'BE' or upper(x.country)='BELGIQUE'  then country_code='BE';
            when substr(x.tva_num,1,2) = 'LU' or upper(x.country)='LUXEMBOURG'  then                country_code='LU';
            when substr(x.tva_num,1,2) <> '' then country_code=upper(substr(x.tva_num,1,2));
            else
                country_code='';
        end case;
            raise notice 'tva_num % country % country_code % jft_id %s',x.tva_num,x.country,country_code,x.jft_id;
        if country_code <> '' then
            update fiche_detail set ad_value=country_code where jft_id=x.jft_id;
        end if;
    end loop;
end;
$$
LANGUAGE plpgsql;


select guess_country_code();

insert into version (val,v_description) values (207,'Guess country code ');
commit;