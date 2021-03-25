begin ;
alter table forecast_cat rename to forecast_category;
alter table forecast_category  alter f_id set not null;
insert into version (val,v_description) values (154,'Rewriting of FORECAST');
commit;