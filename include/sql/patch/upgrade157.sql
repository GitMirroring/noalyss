begin;

alter table user_filter add column uf_currency_code int;
comment on column user_filter.uf_currency_code is 'correspond to currency.id';

insert into version (val,v_description) values (158,'Filter Currency search');
commit ;