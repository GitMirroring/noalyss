begin;
update menu_ref set me_type='PR' where me_code = 'PDF:operation_detail';
insert into version (val,v_description) values (206,'Missing me_type');
commit;
