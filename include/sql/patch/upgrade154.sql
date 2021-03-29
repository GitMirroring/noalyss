begin;
create table parameter_extra (id serial primary key , pe_code text,pe_label text ,pe_value text);
alter table parameter_extra add constraint pe_code_ux unique (pe_code);
comment on table parameter_extra is 'Extra parameter for the folder';
comment on column parameter_extra.pe_code is 'Code used in the Document:generate';
comment on column parameter_extra.pe_label  is 'Label description';
comment on column parameter_extra.pe_value is 'Value which will replace the tag in Document:generate';


CREATE OR REPLACE FUNCTION comptaproc.transform_to_code(p_account text)
 RETURNS text
 AS $function$

declare

sResult text;

begin
sResult := lower(p_account);

sResult := translate(sResult,E'éèêëàâäïîüûùöôç','eeeeaaaiiuuuooc');
sResult := translate(sResult,E' $€µ£%.+-/\\!(){}(),;&|"#''^<>*','');

return upper(sResult);

end;
$function$ 
LANGUAGE plpgsql;


create or replace function comptaproc.t_parameter_extra_code () returns trigger
as $fct$
begin
	new.pe_code := comptaproc.transform_to_code (new.pe_code);
        return new;
end;
$fct$
language plpgsql;


create trigger trigger_parameter_extra_format_code_biu  
before  insert or update on parameter_extra for each row execute procedure comptaproc.t_parameter_extra_code();

create or replace function tmp_1 () returns void
as 
$fct$
declare 
	sCountry text;
begin
	select pr_value into sCountry from parameter where pr_id='MY_COUNTRY';
	
if sCountry = 'BE' then
	insert into parameter_extra(pe_code,pe_label) values ('RPM','RPM'), ('ONSS','Numéro ONSS');

end if;	

if sCountry = 'FR' then 
	insert into parameter_extra(pe_code,pe_label) values ('SIRET','SIRET'), ('SIREN','SIREN');
end if;
insert into parameter_extra (pe_code,pe_label) values ('email_company','Email Société'),('web_company','Site Web');

end;
$fct$
language plpgsql;

select tmp_1();

drop function tmp_1;
insert into version (val,v_description) values (155,'Rewriting of COMPANY add extra parameter');


commit;