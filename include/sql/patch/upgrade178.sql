begin;
update menu_ref set me_menu='Catégorie fiche' where me_code='CCARD';
alter table attr_def add column ad_default_order int;
comment on column attr_def.ad_default_order is 'Default order of the attribute';








delete from attr_min where ad_id=30 and frd_id=2;
insert into attr_min (frd_id,ad_id)  select 2,5 from fiche_def where frd_id=2 on conflict (frd_id,ad_id) do nothing;
insert into attr_min (frd_id,ad_id)  select 3,5 from fiche_def where frd_id=3 on conflict (frd_id,ad_id) do nothing;


insert into attr_min (frd_id,ad_id)  select 3,5 from fiche_def where frd_id=3  on conflict (frd_id ,ad_id ) do nothing;

insert into attr_min (frd_id,ad_id)  select 14,9 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,12 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,14 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,16 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,17 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,18 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,24 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,26 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 14,5 from fiche_def where frd_id=14  on conflict (frd_id ,ad_id ) do nothing;

insert into attr_min (frd_id,ad_id)  select 25,14 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 25,32 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 25,27 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 25,18 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 25,27 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 25,32 from fiche_def where frd_id=25  on conflict (frd_id ,ad_id ) do nothing;

insert into attr_min (frd_id,ad_id) select 13,5 from fiche_def_ref where frd_id=13  on conflict (frd_id ,ad_id ) do nothing;

insert into attr_min (frd_id,ad_id)  select 8,5 from fiche_def where frd_id=8  on conflict (frd_id ,ad_id ) do nothing;


insert into attr_min (frd_id,ad_id)  select 7,5 from fiche_def where frd_id=7  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 7,11 from fiche_def where frd_id=7  on conflict (frd_id ,ad_id ) do nothing;


insert into attr_min (frd_id,ad_id)  select 6,5 from fiche_def where frd_id=6  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 6,9 from fiche_def where frd_id=6  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 6,25 from fiche_def where frd_id=6  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 6,33 from fiche_def where frd_id=6  on conflict (frd_id ,ad_id ) do nothing;

delete from attr_min where frd_id =10 and ad_id =12;

insert into attr_min (frd_id,ad_id)  select 10,5 from fiche_def where frd_id=10  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 10,9 from fiche_def where frd_id=10  on conflict (frd_id ,ad_id ) do nothing;

delete from attr_min where frd_id =12 and ad_id =12;

insert into attr_min (frd_id,ad_id)  select 12,5 from fiche_def where frd_id=12  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 12,9 from fiche_def where frd_id=12  on conflict (frd_id ,ad_id ) do nothing;

delete from attr_min where frd_id =4 and ad_id =4;
insert into attr_min (frd_id,ad_id)  select 4,5 from fiche_def where frd_id=4  on conflict (frd_id ,ad_id ) do nothing;
insert into attr_min (frd_id,ad_id)  select 4,26 from fiche_def where frd_id=4  on conflict (frd_id ,ad_id ) do nothing;;

delete from attr_min where frd_id =1 and ad_id =15;
insert into attr_min (frd_id,ad_id)  select 1,5 from fiche_def where frd_id=1  on conflict (frd_id ,ad_id ) do nothing;

insert into attr_def values (34,'Site Web','text',22,null,1,157);
insert into attr_min values(8,34), (9,34),(14,34),(4,34);

-- select 'update attr_def set ad_default_order = '||ad_default_order::text||' where ad_id='||ad_id::text||';' from attr_def ad order by ad_default_order;
update attr_def set ad_default_order = 10 where ad_id=1;
update attr_def set ad_default_order = 20 where ad_id=32;
update attr_def set ad_default_order = 30 where ad_id=9;
update attr_def set ad_default_order = 40 where ad_id=34;
update attr_def set ad_default_order = 50 where ad_id=30;
update attr_def set ad_default_order = 60 where ad_id=12;
update attr_def set ad_default_order = 70 where ad_id=25;
update attr_def set ad_default_order = 80 where ad_id=13;
update attr_def set ad_default_order = 90 where ad_id=18;
update attr_def set ad_default_order = 100 where ad_id=27;
update attr_def set ad_default_order = 110 where ad_id=17;
update attr_def set ad_default_order = 120 where ad_id=26;
update attr_def set ad_default_order = 130 where ad_id=14;
update attr_def set ad_default_order = 140 where ad_id=15;
update attr_def set ad_default_order = 150 where ad_id=16;
update attr_def set ad_default_order = 160 where ad_id=24;
update attr_def set ad_default_order = 170 where ad_id=4;
update attr_def set ad_default_order = 180 where ad_id=3;
update attr_def set ad_default_order = 190 where ad_id=5;
update attr_def set ad_default_order = 200 where ad_id=6;
update attr_def set ad_default_order = 210 where ad_id=7;
update attr_def set ad_default_order = 220 where ad_id=2;
update attr_def set ad_default_order = 230 where ad_id=8;
update attr_def set ad_default_order = 240 where ad_id=11;
update attr_def set ad_default_order = 250 where ad_id=10;
update attr_def set ad_default_order = 260 where ad_id=33;
update attr_def set ad_default_order = 270 where ad_id=19;
update attr_def set ad_default_order = 280 where ad_id=20;
update attr_def set ad_default_order = 290 where ad_id=53;
update attr_def set ad_default_order = 300 where ad_id=21;
update attr_def set ad_default_order = 310 where ad_id=51;
update attr_def set ad_default_order = 320 where ad_id=22;
update attr_def set ad_default_order = 330 where ad_id=50;
update attr_def set ad_default_order = 340 where ad_id=52;
update attr_def set ad_default_order = 350 where ad_id=31;
update attr_def set ad_default_order = 9999 where ad_id=23;



insert into version (val,v_description) values (179,'Improve Card Attribut - default order');
commit;
