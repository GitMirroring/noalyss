begin;
insert into document_option (do_code,document_type_id,do_enable,do_option) 
    select 'videoconf_server',dt_id,'1','https://www.free-solutions.org/' 
    from 
document_type;
insert into version (val,v_description) values (153,'Add videoconf server');
commit;