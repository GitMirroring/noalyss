-- change color selected menu
update parm_appearance set a_value='#000074' where a_code='MENU1-SELECTED' and a_value='#7191ea';
update parm_appearance set a_value='#FAFAFA' where a_code='BODY' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#FAFAFA' where a_code='FOLDER' and lower(a_value)='#ffffff';
update parm_appearance set a_value='#506cb8 ' where a_code='MENU1-SELECTED' and lower(a_value)='#000074 ';

ALTER TABLE public.todo_list ALTER COLUMN tl_date drop NOT NULL;


update menu_ref set me_javascript='bookmark.show(<DOSSIER>)' where me_code='BOOKMARK';

alter table tool_uos add created_date timestamp default now();
update "parameter" set pr_id='MY_COUNTRY_CODE' where pr_id='MY_COUNTRY';
update "parameter" set pr_id='MY_PHONE' where pr_id='MY_TEL';
update "parameter" set pr_id='MY_POSTCODE' where pr_id='MY_CP';
update "parameter" set pr_id='MY_CITY' where pr_id='MY_COMMUNE';
update "parameter" set pr_id='MY_COUNTRY' where pr_id='MY_PAYS';

insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(55,'SIREN','text',20,1,14);
insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(56,'SIRET','text',20,1,15);

insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(58,'PEPPOL ID','text',20,1,15);
insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order) values(59,'Type de quantité','text',20,1,15);

create table country_code_ref(
    cc_code char(2) primary key,
    cc_name text not null);

insert into country_code_ref values ('AF',' Afghanistan'),
('AL',' Albania'),
('DZ',' Algeria'),
('AS',' American Samoa'),
('AD',' Andorra'),
('AO',' Angola'),
('AG',' Antigua and Barbuda'),
('AR',' Argentina'),
('AM',' Armenia'),
('AU',' Australia'),
('AT',' Austria'),
('AZ',' Azerbaijan'),
('BS',' Bahamas'),
('BH',' Bahrain'),
('BD',' Bangladesh'),
('BB',' Barbados'),
('BY',' Belarus'),
('BE',' Belgium'),
('BZ',' Belize'),
('BJ',' Benin'),
('BM',' Bermuda'),
('BT',' Bhutan'),
('BO',' Bolivia'),
('BA',' Bosnia and Herzegovina'),
('BW',' Botswana'),
('BR',' Brazil'),
('BN',' Brunei Darussalam'),
('BG',' Bulgaria'),
('BF',' Burkina Faso'),
('BI',' Burundi'),
('KH',' Cambodia'),
('CM',' Cameroon'),
('CA',' Canada'),
('CV',' Cape Verde'),
('KY',' Cayman Islands'),
('CF',' Central African Republic'),
('TD',' Chad'),
('CL',' Chile'),
('CN',' China'),
('CO',' Colombia'),
('KM',' Comoros'),
('CG',' Congo'),
('CD',' Congo'),
('CR',' Costa Rica'),
('CI',' Côte d''Ivoire'),
('HR',' Croatia'),
('CU',' Cuba'),
('CY',' Cyprus'),
('CZ',' Czech Republic'),
('DK',' Denmark'),
('DJ',' Djibouti'),
('DM',' Dominica'),
('DO',' Dominican Republic'),
('EC',' Ecuador'),
('EG',' Egypt'),
('SV',' El Salvador'),
('GQ',' Equatorial Guinea'),
('ER',' Eritrea'),
('EE',' Estonia'),
('SZ',' Eswatini'),
('ET',' Ethiopia'),
('FJ',' Fiji'),
('FI',' Finland'),
('FR',' France'),
('GA',' Gabon'),
('GM',' Gambia'),
('GE',' Georgia'),
('DE',' Germany'),
('GH',' Ghana'),
('GR',' Greece'),
('GD',' Grenada'),
('GU',' Guam'),
('GT',' Guatemala'),
('GN',' Guinea'),
('GW',' Guinea-Bissau'),
('GY',' Guyana'),
('HT',' Haiti'),
('HN',' Honduras'),
('HK',' Hong Kong'),
('HU',' Hungary'),
('IS',' Iceland'),
('IN',' India'),
('ID',' Indonesia'),
('IR',' Iran'),
('IQ',' Iraq'),
('IE',' Ireland'),
('IL',' Israel'),
('IT',' Italy'),
('JM',' Jamaica'),
('JP',' Japan'),
('JO',' Jordan'),
('KZ',' Kazakhstan'),
('KE',' Kenya'),
('KI',' Kiribati'),
('KP',' Korea (North)'),
('KR',' Korea (South)'),
('KW',' Kuwait'),
('KG',' Kyrgyzstan'),
('LA',' Lao PDR'),
('LV',' Latvia'),
('LB',' Lebanon'),
('LS',' Lesotho'),
('LR',' Liberia'),
('LY',' Libya'),
('LI',' Liechtenstein'),
('LT',' Lithuania'),
('LU',' Luxembourg'),
('MG',' Madagascar'),
('MW',' Malawi'),
('MY',' Malaysia'),
('MV',' Maldives'),
('ML',' Mali'),
('MT',' Malta'),
('MH',' Marshall Islands'),
('MR',' Mauritania'),
('MU',' Mauritius'),
('MX',' Mexico'),
('FM',' Micronesia'),
('MD',' Moldova'),
('MC',' Monaco'),
('MN',' Mongolia'),
('ME',' Montenegro'),
('MA',' Morocco'),
('MZ',' Mozambique'),
('MM',' Myanmar'),
('NA',' Namibia'),
('NR',' Nauru'),
('NP',' Nepal'),
('NL',' Netherlands'),
('NZ',' New Zealand'),
('NI',' Nicaragua'),
('NE',' Niger'),
('NG',' Nigeria'),
('MK',' North Macedonia'),
('NO',' Norway'),
('OM',' Oman'),
('PK',' Pakistan'),
('PW',' Palau'),
('PS',' Palestine'),
('PA',' Panama'),
('PG',' Papua New Guinea'),
('PY',' Paraguay'),
('PE',' Peru'),
('PH',' Philippines'),
('PL',' Poland'),
('PT',' Portugal'),
('PR',' Puerto Rico'),
('QA',' Qatar'),
('RO',' Romania'),
('RU',' Russian Federation'),
('RW',' Rwanda'),
('WS',' Samoa'),
('SM',' San Marino'),
('ST',' Sao Tome and Principe'),
('SA',' Saudi Arabia'),
('SN',' Senegal'),
('RS',' Serbia'),
('SC',' Seychelles'),
('SL',' Sierra Leone'),
('SG',' Singapore'),
('SK',' Slovakia'),
('SI',' Slovenia'),
('SB',' Solomon Islands'),
('SO',' Somalia'),
('ZA',' South Africa'),
('SS',' South Sudan'),
('ES',' Spain'),
('LK',' Sri Lanka'),
('SD',' Sudan'),
('SR',' Suriname'),
('SE',' Sweden'),
('CH',' Switzerland'),
('SY',' Syrian Arab Republic'),
('TW',' Taiwan'),
('TJ',' Tajikistan'),
('TZ',' Tanzania'),
('TH',' Thailand'),
('TL',' Timor-Leste'),
('TG',' Togo'),
('TO',' Tonga'),
('TT',' Trinidad and Tobago'),
('TN',' Tunisia'),
('TR',' Turkey'),
('TM',' Turkmenistan'),
('TV',' Tuvalu'),
('UG',' Uganda'),
('UA',' Ukraine'),
('AE',' United Arab Emirates'),
('GB',' United Kingdom'),
('US',' United States of America'),
('UY',' Uruguay'),
('UZ',' Uzbekistan'),
('VU',' Vanuatu'),
('VE',' Venezuela'),
('VN',' Vietnam'),
('YE',' Yemen'),
('ZM',' Zambia'),
('ZW',' Zimbabwe'); 

insert into attr_def (ad_id,ad_text,ad_type,ad_size,ad_search_followup,ad_default_order, ad_extra) values(57,'Code pays','select',20,1,15,'select cc_code,format(''%s %s'',cc_code,cc_name) from country_code_ref order by 1');

insert into parameter_extra(pe_code,pe_label) values ('INVOICE_EMAIL_COMPANY','Email pour la facturation') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('INVOICE_CONTACT_NAME','Nom du service pour la facturation') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('COMPANY_LEGAL_REGISTRATION','Nom complet de la société') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('COMPANY_LEGAL_ENTITY','Forme légal de la société (SRL,ASBL,AISBL,...') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('COMPANY_BANK_IBAN','Compte en banque (IBAN)') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('COMPANY_BANK_BIC','BIC Bank Identification Code') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('COMPANY_UBL_ID','Identifiant PEPPOL') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('SIREN','n° SIREN') on conflict do nothing;
insert into parameter_extra(pe_code,pe_label) values ('SIRET','n° SIRET') on conflict do nothing;


-- for all customers and suppliers add new attribut : FICHE_DEF_REF.FRD_ID 8 and 9 ??
-- TVA , PEPPOL ID , SIRENE , SIRET ??

ALTER TABLE TVA_RATE ADD TVA_PEPPOL_CODE varchar(3);
comment on column tva_rate.Tva_peppol_code is 'Code for Peppol : S standard,Z zéro, AE Autoliquidation ,K autoliquidation intra, G : exempté TVA pour export';

drop VIEW public.v_tva_rate;

CREATE OR REPLACE VIEW public.v_tva_rate
AS SELECT tva_id,
          tva_rate,
          tva_code,
          tva_label,
          tva_comment,
          tva_reverse_account,
          split_part(tva_poste, ','::text, 1) AS tva_purchase,
          split_part(tva_poste, ','::text, 2) AS tva_sale,
          tva_both_side,
          tva_payment_purchase,
          tva_payment_sale,
          tva_peppol_code
FROM tva_rate;

create table quantity_code_ref(
    qc_code text not null primary key,
    qc_label text not null
);
insert into quantity_code_ref values 
('EA','Each — unité pièce'),
('C62','unité'),
('ANN','Année'),
('MON','Mois'),
('DAY','Day — jour'),
('HUR','Hour — heure'),
('MIN','Minute — minute'),
('SEC','Second — seconde'),
('KG','Kilogram — kilogramme'),
('G','Gram — gramme'),
('LB','Pound (pound) — livre'),
('LTR','Liter — litre'),
('MTR','Meter — mètre'),
('CM','Centimeter — centimètre'),
('MM','Millimeter — millimètre'),
('PK','Paket — paquet'),
('BX','Box — boîte'),
('PR','Pair — paire'),
('PC','Piece (pièce)'),
('LOT','Lot — lot'),
('SET','Set — ensemble'),
('ROLL','Roll — rouleau'),
('COLL','Collection — collection');


insert into "parameter" values ('MY_INVOICE_FORMAT','BASIC');

alter table jrn add jr_document_xml oid;

comment on column jrn.jr_document_xml is 'OID of the XML files (e-invoice) used only for XML';


 create sequence seq_doc_type_stdinv;
comment on sequence seq_doc_type_stdinv is 'Sequence for standard invoice';


INSERT INTO public.menu_ref (me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue) 
VALUES('RAW:xml-invoice', 'Exporte la facture XML', 'export_xml-invoice.php', NULL, 'exporte la facture électronique en XML', NULL, NULL, 'PR', NULL);

insert into profile_menu (me_code,p_id, p_type_display) select 'RAW:xml-invoice',p_id,'P' from profile;

alter table jrn_note add column n_html text;
comment on column jrn_note.n_html is ' contains  the HTML version from n_text';
update jrn_note set n_html=n_text;

-- replace jrn_add_note
DROP FUNCTION comptaproc.jrn_add_note(int8, text);

CREATE OR REPLACE FUNCTION comptaproc.jrn_add_note(p_jrid bigint, p_note text,p_note_html text)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
declare
	tmp bigint;
begin
	if length(trim(p_note)) = 0 then
	   delete from jrn_note where jr_id= p_jrid;
	   return;
	end if;
	

	select n_id into tmp from jrn_note where jr_id = p_jrid;
	p_note_html := regexp_replace (p_note_html,'<script','<-script','ig');

	if FOUND then
	   update jrn_note set n_text=trim(p_note),n_note_html=p_note_html where jr_id = p_jrid;
	else 
	   insert into jrn_note (jr_id,n_text,n_html) values ( p_jrid, p_note,p_note_html);

	end if;
	
	return;
end;
$function$
;

CREATE TABLE public.parm_mail_server (
	pe_id int4 GENERATED ALWAYS AS IDENTITY( INCREMENT BY 1 MINVALUE 1 MAXVALUE 2147483647 START 1 CACHE 1 NO CYCLE) NOT NULL, -- pk
	pe_name varchar NULL, -- Config. name
	pe_parameter text NOT NULL, -- key for json
	pe_value text NULL, -- value of the key
	CONSTRAINT param_email_pk PRIMARY KEY (pe_id),
	CONSTRAINT parm_email_unique UNIQUE (pe_name, pe_parameter)
);
COMMENT ON TABLE public.parm_mail_server IS 'Parameters for email server';

-- Column comments

COMMENT ON COLUMN public.parm_mail_server.pe_id IS 'pk';
COMMENT ON COLUMN public.parm_mail_server.pe_name IS 'Config. name';
COMMENT ON COLUMN public.parm_mail_server.pe_parameter IS 'key for json';
COMMENT ON COLUMN public.parm_mail_server.pe_value IS 'value of the key';

-- install menu 

insert into menu_ref (me_code,me_menu,me_file,me_description,me_type,me_description_etendue)
values('C0ML','Email','email_setting.inc.php','Configuration email','ME','Configuration de l''envoi d''emails ');

insert into profile_menu 
(me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep)
select me_code,'CFG',1,2,'E',0,(select distinct m2.pm_id from profile_menu m2 where m2.me_code='CFG' limit 1) from menu_ref where me_code='C0ML';


create table parameter_internal (pi_id text not null primary key, pi_value text);
comment on table parameter_internal  is 'Internal parameter, used by the application , it can''t not be changed by the interface';

create or replace function comptaproc.fill_internal_parameter()
returns text
as
$$
declare
	str_country text;
	n_found int;
begin
	select lower(pr_value) into str_country  from "parameter" where pr_id = 'MY_COUNTRY';
	if str_country = 'belgique' or str_country  = 'be' then
		insert into parameter_internal values ('COUNTRY_CODE','BE') on conflict do nothing;
		return 'BE';
	end if ;
	if str_country  = 'france' or str_country  = 'fr' then
		insert into parameter_internal values ('COUNTRY_CODE','FR') on conflict do nothing;
		return 'FR';
	end if ;
	
	select 1 into n_found from bilan where b_file_template='document/fr_fr/fr_plan_abrege_perso_cr1000.form';

	if n_found is NULL then 
		insert into parameter_internal values ('COUNTRY_CODE','BE') on conflict do nothing;
		return 'BE';
	end if;
	insert into parameter_internal values ('COUNTRY_CODE','FR') on conflict do nothing;
		return 'FR';
end;
$$
language plpgsql;

select comptaproc.fill_internal_parameter();
-- insert new attributes for PEPPOL, SIREN and QUANTYTI
insert into jnt_fic_attr (fd_id,ad_id,jnt_order) 
	select f1.fd_id , 58,15
	from fiche_def f1 
	join fiche_def f2 on (f1.fd_id=f2.fd_id) 
	where f2.frd_id in (8,9)
	on conflict do nothing;

insert into jnt_fic_attr (fd_id,ad_id,jnt_order) 
	select f1.fd_id , 57,16
	from fiche_def f1 
	join fiche_def f2 on (f1.fd_id=f2.fd_id) 
	where f2.frd_id in (8,9)
	on conflict do nothing;

insert into jnt_fic_attr (fd_id,ad_id,jnt_order) 
	select f1.fd_id , 55,16
	from fiche_def f1 
	join fiche_def f2 on (f1.fd_id=f2.fd_id) 
	where f2.frd_id in (8,9)
	on conflict do nothing;

insert into jnt_fic_attr (fd_id,ad_id,jnt_order) 
	select f1.fd_id , 59,90
	from fiche_def f1 
	join fiche_def f2 on (f1.fd_id=f2.fd_id) 
	where f2.frd_id in (1,2)
	on conflict do nothing;

-- Add a attribute PEPPOL ID for customer and supplier in Belgium only
-- for the card category 
-- Plus, compute a default value
--
CREATE OR REPLACE FUNCTION comptaproc.update_peppol ()
  RETURNS int2
 AS
$BODY$
declare
    n_card_id int;
    vat_number text;
    record_card RECORD;
    result_fct int;
begin

result_fct := 0;

for record_card in select ad_value,f1.f_id   
	from fiche_detail f1
	join fiche f2 using(f_id)
	join fiche_def f3 using (fd_id)
	where ad_id=13 and LOWER (ad_value ) like 'be%'
	and f3.frd_id  = 9
loop	
	vat_number := regexp_replace(record_card.ad_value,'\D','','g');
	if length(vat_number) = 10 then
		update fiche_detail set ad_value = '0208:'||vat_number where f_id=record_card.f_id and ad_id=58;
		RESULT_fct := result_fct + 1;
	end if;
end loop;

return result_fct;
end;
$BODY$
LANGUAGE plpgsql;

select comptaproc.update_peppol();


CREATE TABLE public.jrn_sup_document (
	js_id int8 GENERATED BY DEFAULT AS IDENTITY( INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1 NO CYCLE) NOT NULL, -- PK
	js_mimetype text NOT NULL, -- Mimetype
	js_lob oid NOT NULL, -- OID
	jr_id int8 NOT NULL, -- FK to jrn
	js_filename text not null,
	js_description text , 
	js_cbc_id text,
	CONSTRAINT jrn_sup_document_pkey PRIMARY KEY (js_id),
	CONSTRAINT jrn_sup_document_jrn_fk FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON DELETE SET NULL ON UPDATE CASCADE
);
COMMENT ON TABLE public.jrn_sup_document IS 'Supplement document for operation';

-- Column comments

COMMENT ON COLUMN public.jrn_sup_document.js_id IS 'PK';
COMMENT ON COLUMN public.jrn_sup_document.js_mimetype IS 'Mimetype';
COMMENT ON COLUMN public.jrn_sup_document.js_lob IS 'OID';
COMMENT ON COLUMN public.jrn_sup_document.jr_id IS 'FK to jrn';
COMMENT ON COLUMN public.jrn_sup_document.js_description IS 'Description of document';
COMMENT ON COLUMN public.jrn_sup_document.js_cbc_id IS 'ID in XML';



INSERT INTO public.menu_ref (me_code, me_menu, me_file, me_url, me_description, me_parameter, me_javascript, me_type, me_description_etendue) 
VALUES('RAW:suppl-document', 'Exporte Document supplementaire', 'export_suppl-document.php', NULL, 'télécharge le document supp', NULL, NULL, 'PR', NULL);

insert into profile_menu (me_code,p_id, p_type_display) select 'RAW:suppl-document',p_id,'P' from profile;
