begin;

update attr_def set ad_extra = '[sql] fd_id in (select fd_id from fiche_def where frd_id in (4,8,9,14))' where ad_id=25;

alter table mod_payment rename to payment_method;

insert into version (val,v_description) values (135,'rename table mod_payment');
commit ;