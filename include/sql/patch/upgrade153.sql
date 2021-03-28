begin ;
alter table forecast_cat rename to forecast_category;
alter table forecast_category  alter f_id set not null;
alter table forecast_item add fi_amount_initial numeric(20,4);
alter table forecast_item alter fi_amount_initial set default 0;
insert into version (val,v_description) values (154,'Rewriting of FORECAST');
commit;