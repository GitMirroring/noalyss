alter table attr_min add ad_default_order int;

update attr_min set ad_default_order = a1.ad_default_order from attr_def a1 where a1.ad_id = attr_min.ad_id;