begin;
update attr_def set ad_extra = '[sql] fd_id in (select fd_id from fiche_def where frd_id in (4,8,9,14))' where ad_id=25;
insert into "parameter" (pr_id ) values ('MY_DEFAULT_ROUND_ERROR_DEB');
insert into "parameter" (pr_id ) values ('MY_DEFAULT_ROUND_ERROR_CRED');
drop view if exists v_all_card_currency;

create or replace view v_all_card_currency as 
select sum(oc_amount) as sum_oc_amount,sum(oc_vat_amount) as sum_oc_vat_amount,f_id,j_id
from 
operation_currency
join jrnx using (j_id)
group by f_id,j_id;

commit ;