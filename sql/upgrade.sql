-- auto-generated definition
create table acc_other_tax
(
    ac_id             serial        constraint acc_other_tax_pk            primary key,
    ac_label       text         not null,
    ac_rate numeric (5,2) not null,
    ajrn_def_id    integer[],
    ac_accounting account_type not null
);
comment on table acc_other_tax is 'Other tax ';
comment on column acc_other_tax.ac_label is 'Label of the tax';
comment on column acc_other_tax.ac_rate is 'rate of the tax in percent';
comment on column acc_other_tax.ajrn_def_id is 'array of to FK jrn_def (jrn_def_id)';
comment on column acc_other_tax.ac_accounting is 'FK tmp_pcmn (pcm_val)';

