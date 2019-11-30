begin;
alter table user_filter rename unpaid to operation_filter;
alter table user_filter alter column operation_filter type  text;
update user_filter set operation_filter = 'paid' where operation_filter is not null;
update user_filter set operation_filter = 'all' where operation_filter is  null;
comment on column user_filter.operation_filter is 'Status of the operation  : paid, unpaid or all operation';
alter table user_filter alter operation_filter set not null;

insert into version (val,v_description) values (141,'Search filter with operation status');
commit ;
