begin;

alter table jrn_def add column jrn_def_negative_amount char(1) default '0';
update jrn_def set jrn_def_negative_amount = '0';
alter table jrn_def add constraint negative_amount_ck check (jrn_def_negative_amount in ('1','0'));
alter table jrn_def alter jrn_def_negative_amount set not null;
comment on column jrn_def.jrn_def_negative_amount is '1 echo a warning if you are not using an negative amount, default 0 for no warning';
alter table jrn_def add column jrn_def_negative_warning text;
update jrn_def set jrn_def_negative_warning = 'Attention, ce journal doit utiliser des montants négatifs';
comment on column jrn_def.jrn_def_negative_warning is 'Yell a warning if the amount if not negative , in the case of jrn_def_negative_amount is Y';



insert into version (val,v_description) values (137,'Ledger warning');
commit ;