begin;

create or replace function tmp_1 () returns void
as 
$fct$
declare 
	sCountry text;
        nOhada int;
begin
	
-- set my_country for OHADA
select count(*) into nOhada from tmp_pcmn where pcm_val='886' ;

if nOhada = 1 then 
    update parameter set pr_value='AF' where pr_id='MY_COUNTRY';
end if;

select pr_value into sCountry from parameter where pr_id='MY_COUNTRY';

if sCountry = 'BE' then
    insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use)
values ('755','Ecart de conversion','75','PRO','Y') on conflict do nothing;
    insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use)
values ('655','Ecart de conversion','65','CHA','Y') on conflict do nothing;

    update  public."parameter"  set pr_value = '755' where pr_id= 'MY_DEFAULT_ROUND_ERROR_CRED';
    update  public."parameter"  set pr_value =  '655' where pr_id= 'MY_DEFAULT_ROUND_ERROR_DEB';
    

end if;	

if sCountry = 'FR' then 
    insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use)
values ('758','Ecart de conversion','75','PRO','Y') on conflict do nothing;
    insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use)
values ('658','Ecart de conversion ','65','CHA','Y') on conflict do nothing;

    update  public."parameter"  set pr_value = '758' where pr_id= 'MY_DEFAULT_ROUND_ERROR_CRED';
    update  public."parameter"  set pr_value =  '658' where pr_id= 'MY_DEFAULT_ROUND_ERROR_DEB';
    
end if;



end;
$fct$
language plpgsql;

select tmp_1();

drop function tmp_1();
insert into version (val,v_description) values (156,'insert default accounting');


commit;
