begin;


create or replace view v_contact as
with contact_data as (select f.f_id , f.fd_id from fiche f join fiche_def fd on (f.fd_id=fd.fd_id) where fd.frd_id=16)
select f_id,
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

insert into attr_min values (16,32);

insert into jnt_fic_attr (fd_id,jnt_order,ad_id) select fd_id,10,32 from fiche_def where frd_id=16 on conflict(fd_id,ad_id) do nothing;
update attr_def set ad_text='Tél. Portable' where ad_id=27;


insert into version (val,v_description) values (178,'Correct contact');
commit;