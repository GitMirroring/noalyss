begin;
insert into "parameter" (pr_id ) values ('MY_DEFAULT_ROUND_ERROR_DEB');
insert into "parameter" (pr_id ) values ('MY_DEFAULT_ROUND_ERROR_CRED');
drop view if exists v_all_card_currency;

create or replace view v_all_card_currency as 
select sum(oc_amount) as sum_oc_amount,sum(oc_vat_amount) as sum_oc_vat_amount,f_id,j_id
from 
operation_currency
join jrnx using (j_id)
group by f_id,j_id;


insert into version (val,v_description) values (133,'Currency : default accounting for currency difference  ');
commit ;