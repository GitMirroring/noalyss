update attr_def set ad_extra = '[sql] fd_id in (select fd_id from fiche_def where frd_id in (4,8,9,14))' where ad_id=25;

alter table mod_payment rename to payment_method;

alter table payment_method add column type_payment_id varchar(6);


comment on column payment_method.type_payment_id  is 'Type of payment method';

create table type_payment_ref (id varchar(6 ) primary key,tp_description varchar(80) not null);

insert into type_payment_ref (id,tp_description) values ('CACRD','Carte de crédit'),
                                                        ('CHCK','Chèque'),
                                                        ('CASH','Liquide'),
                                                        ('ELECT','Electronique'),
                                                        ('VIRT','Virement');
update payment_method set type_payment_id = 'VIRT';

alter table payment_method add constraint type_payment_ref_fk foreign key (type_payment_id) references type_payment_ref(id);

alter table payment_method alter type_payment_id set not null;