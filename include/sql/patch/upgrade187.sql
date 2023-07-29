begin;

alter table public.user_filter add tva_id_search integer;

comment on column public.user_filter.tva_id_search is 'VAT id ';

insert into version (val,v_description) values (188,'Filter for VAT id');
commit;