begin;

insert into menu_ref (me_code,me_description ,me_file) values ('PDF:operation_detail','Export Operation','export_operation_pdf.php');
insert into profile_menu(me_code,p_id,p_type_display ,pm_default ) select 'PDF:operation_detail',p_id,'P',0 from profile ;

insert into version (val,v_description) values (144,'Export Operation PDF');

commit;
