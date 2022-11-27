begin;
insert into menu_default (md_code,me_code)values ('code_feenote','COMPTA/MENUACH/ACH');

insert into document_option (do_code,document_type_id,do_enable)
select 'make_feenote',10,1 from "document_type" where dt_id=10
on conflict on constraint document_option_un
    do update set do_enable=1
;


insert into version (val,v_description) values (181,'Make feenote from Management');
commit;
