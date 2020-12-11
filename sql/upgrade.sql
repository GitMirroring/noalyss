-- improve vw_fiche_attr
drop index if exists fiche_detail_attr_ix;
create index fiche_detail_attr_ix on fiche_detail (ad_id);
