begin;

delete from menu_ref where me_code='PDF:fiche';

insert into version (val,v_description) values (190,'remove dead code');
commit;