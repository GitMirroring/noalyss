begin;

drop view if exists v_contact ;


create view v_contact as
with contact_data as (select f.f_id , f.f_enable,f.fd_id from fiche f join fiche_def fd on (f.fd_id=fd.fd_id) where fd.frd_id=16)
select f_id,f_enable,fd_id,
       (select ad_value from fiche_detail where ad_id=32 and f_id=cd.f_id) as contact_fname,
       (select ad_value from fiche_detail where ad_id=1 and f_id=cd.f_id) as contact_name,
       (select ad_value from fiche_detail where ad_id=23 and f_id=cd.f_id) as contact_qcode,
       (select ad_value from fiche_detail where ad_id=25 and f_id=cd.f_id) as contact_company,
       (select ad_value from fiche_detail where ad_id=27 and f_id=cd.f_id) as contact_mobile,
       (select ad_value from fiche_detail where ad_id=17 and f_id=cd.f_id) as contact_phone,
       (select ad_value from fiche_detail where ad_id=18 and f_id=cd.f_id) as contact_email,
       (select ad_value from fiche_detail where ad_id=26 and f_id=cd.f_id) as contact_fax,
       cd.fd_id as card_category
from contact_data cd	;

insert into jnt_fic_attr (fd_id,ad_id,jnt_order) select fd_id , 34,40 from fiche_def
        join fiche_def_ref using (frd_id) where frd_id in (4,8,9) on conflict do nothing;

insert into version (val,v_description) values (186,'Correct contact and web site');
commit;