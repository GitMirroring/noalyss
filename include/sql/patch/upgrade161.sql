begin;

alter table poste_analytique  add po_state integer;

alter table poste_analytique alter po_state set default   1;

update poste_analytique  set po_state=1;

alter table poste_analytique alter po_state set not null;

comment  on column poste_analytique.po_state is 'Analytic Account state : 0 disabled 0 enabled ';

insert into version (val,v_description) values (162,'Analytic accountancy : enable or disable #1983');
commit ;