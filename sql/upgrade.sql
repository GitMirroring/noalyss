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


alter table tva_rate add column vx_code text;

COMMENT ON COLUMN public.tva_rate.vx_code IS 'Peppol: exemption code for VAT';


-- public.vatex_code definition

-- Drop table

-- DROP TABLE public.vatex_code;


CREATE TABLE public.vatex_code ( vx_code text primary key, vx_country varchar(5) , vx_code_name varchar(256) , vx_description text, vx_remark text);

INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-79-C','eu','Exonération en vertu de l''article 79, point c), de la directive 2006/112/CE du Conseil','Exemptions relatives au remboursement des dépenses.','Le remboursement des dépenses ne constitue pas une exonération au sens de la directive TVA, mais peut être traité comme tel dans le cadre de la norme EN16931.'),
	 ('VATEX-EU-132','eu','Exonération fondée sur l''article 132 de la directive 2006/112/CE du Conseil','Exemptions pour certaines activités d''intérêt public.',''),
	 ('VATEX-EU-132-1A','eu','Exonération en vertu de l''article 132, paragraphe 1, point a), de la directive 2006/112/CE du Conseil','La fourniture par les services postaux publics de services autres que le transport de passagers et les services de télécommunications, ainsi que la fourniture de biens accessoires à ces services.',''),
	 ('VATEX-EU-132-1B','eu','Exonération en vertu de l''article 132, paragraphe 1, point b), de la directive 2006/112/CE du Conseil','Soins hospitaliers et médicaux et activités étroitement liées exercées par des organismes de droit public ou, dans des conditions sociales comparables à celles applicables aux organismes de droit public, par des hôpitaux, des centres de traitement ou de diagnostic médical et d''autres établissements dûment reconnus de nature similaire.',''),
	 ('VATEX-EU-132-1C','eu','Exonération sur la base de l''article 132, paragraphe 1, point c), de la directive 2006/112/CE du Conseil','La prestation de soins médicaux dans l''exercice des professions médicales et paramédicales telles que définies par l''État membre concerné.',''),
	 ('VATEX-EU-132-1D','eu','Exonération en vertu de l''article 132, paragraphe 1, point d), de la directive 2006/112/CE du Conseil','L''approvisionnement en organes humains, en sang et en lait.',''),
	 ('VATEX-EU-132-1E','eu','Exonération en vertu de l''article 132, paragraphe 1, point e), de la directive 2006/112/CE du Conseil','La prestation de services par les techniciens dentaires dans le cadre de leur activité professionnelle et la fourniture de prothèses dentaires par les dentistes et les techniciens dentaires.',''),
	 ('VATEX-EU-132-1F','eu','Exonération en vertu de l''article 132, paragraphe 1, point f), de la directive 2006/112/CE du Conseil','La prestation de services par des groupements autonomes de personnes, qui exercent une activité exonérée de la TVA ou pour laquelle ils ne sont pas assujettis, dans le but de fournir à leurs membres les services directement nécessaires à l''exercice de cette activité, lorsque ces groupements se contentent de réclamer à leurs membres le remboursement exact de leur part des dépenses communes, à condition que cette exonération ne soit pas susceptible de provoquer des distorsions de concurrence.',''),
	 ('VATEX-EU-132-1G','eu','Exonération fondée sur l''article 132, paragraphe 1, point g), de la directive 2006/112/CE du Conseil','La fourniture de services et de biens étroitement liés à l''action sociale et à la protection sociale, y compris ceux fournis par les maisons de retraite, par des organismes de droit public ou par d''autres organismes reconnus par l''État membre concerné comme étant consacrés au bien-être social.',''),
	 ('VATEX-EU-132-1H','eu','Exonération sur la base de l''article 132, paragraphe 1, point h), de la directive 2006/112/CE du Conseil','La fourniture de services et de biens étroitement liés à la protection des enfants et des jeunes par des organismes de droit public ou par d''autres organisations reconnues par l''État membre concerné comme étant consacrées au bien-être social ;','');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-132-1I','eu','Exonération en vertu de l''article 132, paragraphe 1, point i), de la directive 2006/112/CE du Conseil',' La fourniture d''enseignement aux enfants ou aux jeunes, d''enseignement scolaire ou universitaire, de formation professionnelle ou de reconversion, y compris la fourniture de services et de biens étroitement liés à ces activités, par des organismes de droit public ayant pour but ou par d''autres organismes reconnus par l''État membre concerné comme ayant des objectifs similaires.',''),
	 ('VATEX-EU-132-1J','eu','Exonération sur la base de l''article 132, paragraphe 1, point j), de la directive 2006/112/CE du Conseil','Cours particuliers dispensés par des enseignants et couvrant l''enseignement scolaire ou universitaire.',''),
	 ('VATEX-EU-132-1K','eu','Exonération en vertu de l''article 132, paragraphe 1, point k), de la directive 2006/112/CE du Conseil','La mise à disposition de personnel par des institutions religieuses ou philosophiques aux fins des activités visées aux points b), g), h) et i) et dans le but d''assurer le bien-être spirituel.',''),
	 ('VATEX-EU-132-1L','eu','Exonération en vertu de l''article 132, paragraphe 1, point l), de la directive 2006/112/CE du Conseil','La fourniture de services, et la fourniture de biens qui y sont étroitement liés, à leurs membres dans leur intérêt commun, en contrepartie d''une cotisation fixée conformément à leurs règles, par des organisations sans but lucratif à caractère politique, syndical, religieux, patriotique, philosophique, philanthropique ou civique, à condition que cette exonération ne soit pas susceptible de provoquer des distorsions de concurrence.',''),
	 ('VATEX-EU-132-1M','eu','Exonération en vertu de l''article 132, paragraphe 1, point m), de la directive 2006/112/CE du Conseil
	 ','La prestation de certains services étroitement liés au sport ou à l''éducation physique par des organismes sans but lucratif à des personnes participant à des activités sportives ou d''éducation physique.',''),
	 ('VATEX-EU-132-1N','eu','Exonération en vertu de l''article 132, paragraphe 1, point n), de la directive 2006/112/CE du Conseil','La prestation de certains services culturels et la fourniture de biens étroitement liés à ces services par des organismes de droit public ou d''autres organismes culturels reconnus par l''État membre concerné.',''),
	 ('VATEX-EU-132-1O','eu','Exonération en vertu de l''article 132, paragraphe 1, point o), de la directive 2006/112/CE du Conseil','La fourniture de services et de biens, par des organisations dont les activités sont exonérées en vertu des points b), g), h), i), l), m) et n), dans le cadre d''événements organisés exclusivement à des fins de collecte de fonds pour leur propre bénéfice, à condition que cette exonération ne soit pas susceptible de provoquer des distorsions de concurrence.',''),
	 ('VATEX-EU-132-1P','eu','Exonération en vertu de l''article 132, paragraphe 1, point p), de la directive 2006/112/CE du Conseil','Prestation de services de transport de personnes malades ou blessées dans des véhicules spécialement conçus à cet effet, par des organismes dûment agréés.',''),
	 ('VATEX-EU-132-1Q','eu','Exonération en vertu de l''article 132, paragraphe 1, point q), de la directive 2006/112/CE du Conseil','Les activités, autres que celles à caractère commercial, exercées par les organismes publics de radio et de télévision.',''),
	 ('VATEX-EU-143','eu','Exonération en vertu de l''article 143 de la directive 2006/112/CE du Conseil','Exonérations à l''importation.','');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-143-1A','eu','Exonération en vertu de l''article 143, paragraphe 1, point a), de la directive 2006/112/CE du Conseil','Importation définitive de biens dont la livraison par un assujetti serait en tout état de cause exonérée sur leur territoire respectif.',''),
	 ('VATEX-EU-143-1B','eu','Exonération en vertu de l''article 143, paragraphe 1, point b), de la directive 2006/112/CE du Conseil','L''importation définitive de biens régis par les directives 69/169/CEE (1), 83/181/CEE (2) et 2006/79/CE (3) du Conseil.',''),
	 ('VATEX-EU-143-1C','eu','Exonération en vertu de l''article 143, paragraphe 1, point c), de la directive 2006/112/CE du Conseil','L''importation définitive de biens, en libre pratique à partir d''un territoire tiers faisant partie du territoire douanier de la Communauté, qui bénéficieraient de l''exonération prévue au point b) s''ils avaient été importés au sens de l''article 30, premier alinéa.',''),
	 ('VATEX-EU-143-1D','eu','Exonération en vertu de l''article 143, paragraphe 1, point d), de la directive 2006/112/CE du Conseil','L''importation de biens expédiés ou transportés à partir d''un territoire tiers ou d''un pays tiers vers un État membre autre que celui où l''expédition ou le transport des biens prend fin, lorsque la livraison de ces biens par l''importateur désigné ou reconnu comme redevable de la TVA en vertu de l''article 201 est exonérée en vertu de l''article 138.',''),
	 ('VATEX-EU-143-1E','eu','Exonération en vertu de l''article 143, paragraphe 1, point e), de la directive 2006/112/CE du Conseil','La réimportation, par la personne qui les a exportées, de marchandises dans l''état où elles ont été exportées, lorsque ces marchandises sont exonérées de droits de douane.',''),
	 ('VATEX-EU-143-1F','eu','Exonération en vertu de l''article 143, paragraphe 1, point f), de la directive 2006/112/CE du Conseil','L''importation, dans le cadre d''accords diplomatiques et consulaires, de marchandises exonérées de droits de douane.',''),
	 ('VATEX-EU-143-1FA','eu','Exonération en vertu de l''article 143, paragraphe 1, point f bis), de la directive 2006/112/CE du Conseil','L''importation de marchandises par la Communauté européenne, la Communauté européenne de l''énergie atomique, la Banque centrale européenne ou la Banque européenne d''investissement, ou par les organismes créés par les Communautés auxquels s''applique le protocole du 8 avril 1965 sur les privilèges et immunités des Communautés européennes, dans les limites et conditions prévues par ce protocole et les accords pris pour son application ou les accords de siège, dans la mesure où cela n''entraîne pas de distorsion de concurrence ;',''),
	 ('VATEX-EU-143-1G','eu','Exonération fondée sur l''article 143, paragraphe 1, point g), de la directive 2006/112/CE du Conseil',' L''importation de biens par des organismes internationaux, autres que ceux visés au point f bis), reconnus comme tels par les autorités publiques de l''État membre d''accueil, ou par les membres de ces organismes, dans les limites et conditions fixées par les conventions internationales instituant les organismes ou par les accords de siège ;',''),
	 ('VATEX-EU-143-1H','eu','Exonération sur la base de l''article 143, paragraphe 1, point h), de la directive 2006/112/CE du Conseil','L''importation de biens, dans les États membres parties au traité de l''Atlantique Nord, par les forces armées d''autres États parties à ce traité pour l''usage de ces forces ou du personnel civil qui les accompagne ou pour l''approvisionnement de leurs mess ou cantines lorsque ces forces participent à l''effort commun de défense.',''),
	 ('VATEX-EU-143-1I','eu','Exonération sur la base de l''article 143, paragraphe 1, point i), de la directive 2006/112/CE du Conseil','Importation de biens par les forces armées du Royaume-Uni stationnées sur l''île de Chypre en vertu du traité d''établissement concernant la République de Chypre, daté du 16 août 1960, destinés à l''usage de ces forces ou du personnel civil qui les accompagne ou à l''approvisionnement de leurs mess ou cantines.','');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-143-1J','eu','Exonération en vertu de l''article 143, paragraphe 1, point j), de la directive 2006/112/CE du Conseil','L''importation dans les ports, par les entreprises de pêche maritime, de leurs captures, non transformées ou après avoir subi une conservation en vue de leur commercialisation, mais avant leur livraison.',''),
	 ('VATEX-EU-143-1K','eu','Exonération en vertu de l''article 143, paragraphe 1, point k), de la directive 2006/112/CE du Conseil','Importation d''or par les banques centrales.',''),
	 ('VATEX-EU-143-1L','eu','Exonération en vertu de l''article 143, paragraphe 1, point l), de la directive 2006/112/CE du Conseil','Importation de gaz par un réseau de gaz naturel ou tout réseau raccordé à un tel réseau ou alimenté par un navire transportant du gaz dans un réseau de gaz naturel ou tout réseau de gazoduc en amont, d''électricité ou d''énergie thermique ou frigorifique par des réseaux de chauffage ou de refroidissement.',''),
	 ('VATEX-EU-144','eu','Exonération en vertu de l''article 144 de la directive 2006/112/CE du Conseil','Exonérations pour les services liés à l''importation de biens',''),
	 ('VATEX-EU-146-1E','eu','Exonération en vertu de l''article 146, paragraphe 1, point e), de la directive 2006/112/CE du Conseil','Exonérations pour les services liés à l''exportation de biens',''),
	 ('VATEX-EU-148','eu','Exonération en vertu de l''article 148 de la directive 2006/112/CE du Conseil','Exonérations liées au transport international.',''),
	 ('VATEX-EU-148-A','eu','Exonération en vertu de l''article 148, point a), de la directive 2006/112/CE du Conseil','Fournitures de carburant pour les navires de transport international commercial',''),
	 ('VATEX-EU-148-B','eu','Exonération en vertu de l''article 148, point b), de la directive 2006/112/CE du Conseil','Fournitures de carburant pour les navires de combat effectuant des transports internationaux.',''),
	 ('VATEX-EU-148-C','eu','Exonération en vertu de l''article 148, paragraphe c), de la directive 2006/112/CE du Conseil','Entretien, modification, affrètement et location de navires de transport international.',''),
	 ('VATEX-EU-148-D','eu','Exonération en vertu de l''article 148, paragraphe d), de la directive 2006/112/CE du Conseil','Prestation d''autres services à des navires de transport commercial international.','');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-148-E','eu','Exonération en vertu de l''article 148, paragraphe e), de la directive 2006/112/CE du Conseil','Fourniture de carburant pour les aéronefs effectuant des vols internationaux.',''),
	 ('VATEX-EU-148-F','eu','Exonération en vertu de l''article 148, paragraphe f), de la directive 2006/112/CE du Conseil','Entretien, modification, affrètement et location d''aéronefs sur les liaisons internationales.',''),
	 ('VATEX-EU-148-G','eu','Exonération en vertu de l''article 148, point g), de la directive 2006/112/CE du Conseil','Prestation d''autres services aux aéronefs sur les routes internationales.',''),
	 ('VATEX-EU-151','eu','Exonération en vertu de l''article 151 de la directive 2006/112/CE du Conseil','Exonérations relatives à certaines opérations assimilées à des exportations.',''),
	 ('VATEX-EU-151-1A','eu','Exonération en vertu de l''article 151, paragraphe 1, point a), de la directive 2006/112/CE du Conseil','La fourniture de biens ou de services dans le cadre d''accords diplomatiques et consulaires.',''),
	 ('VATEX-EU-151-1AA','eu','Exonération en vertu de l''article 151, paragraphe 1, point a bis), de la directive 2006/112/CE du Conseil','La livraison de biens ou la prestation de services à la Communauté européenne, à la Communauté européenne de l''énergie atomique, à la Banque centrale européenne ou à la Banque européenne d''investissement, ou aux organismes créés par les Communautés auxquels s''applique le protocole du 8 avril 1965 sur les privilèges et immunités des Communautés européennes, dans les limites et conditions prévues par ledit protocole et les accords pris pour son application ou les accords de siège, dans la mesure où cela n''entraîne pas de distorsion de concurrence.',''),
	 ('VATEX-EU-151-1B','eu','Exonération fondée sur l''article 151, paragraphe 1, point b), de la directive 2006/112/CE du Conseil','Les livraisons de biens ou les prestations de services effectuées à des organismes internationaux, autres que ceux visés au point a bis), reconnus comme tels par les autorités publiques des États membres d''accueil, et aux membres de ces organismes, dans les limites et conditions prévues par les conventions internationales instituant ces organismes ou par les accords de siège.',''),
	 ('VATEX-EU-151-1C','eu','Exonération sur la base de l''article 151, paragraphe 1, point c), de la directive 2006/112/CE du Conseil','La fourniture de biens ou de services dans un État membre partie au traité de l''Atlantique Nord, destinés soit aux forces armées d''autres États parties à ce traité pour l''usage de ces forces, soit au personnel civil qui les accompagne, soit à l''approvisionnement de leurs mess ou cantines lorsque ces forces participent à l''effort commun de défense.',''),
	 ('VATEX-EU-151-1D','eu','Exonération en vertu de l''article 151, paragraphe 1, point d), de la directive 2006/112/CE du Conseil','La livraison de biens ou la prestation de services à un autre État membre, destinés aux forces armées de tout État partie au traité de l''Atlantique Nord, autre que l''État membre de destination lui-même, pour l''usage de ces forces ou du personnel civil qui les accompagne, ou pour l''approvisionnement de leurs mess ou cantines lorsque ces forces participent à l''effort commun de défense.',''),
	 ('VATEX-EU-151-1E','eu','Exonération en vertu de l''article 151, paragraphe 1, point e), de la directive 2006/112/CE du Conseil','La livraison de biens ou la prestation de services aux forces armées du Royaume-Uni stationnées sur l''île de Chypre en vertu du traité d''établissement concernant la République de Chypre, daté du 16 août 1960, qui sont destinés à l''usage de ces forces ou du personnel civil qui les accompagne, ou à l''approvisionnement de leurs mess ou cantines.','');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-153','eu','Exonération en vertu de l''article 153 de la directive 2006/112/CE du Conseil','Prestation de services par des intermédiaires agissant au nom et pour le compte d''une autre personne. À l''exclusion des agences de voyages.',''),
	 ('VATEX-EU-159','eu','Exonération en vertu de l''article 159 de la directive 2006/112/CE du Conseil','Exonérations pour les services liés à la livraison de biens destinés à être placés sous le régime des entrepôts douaniers, des entrepôts autres que les entrepôts douaniers et des régimes similaires.',''),
	 ('VATEX-EU-309','eu','Exonération en vertu de l''article 309 de la directive 2006/112/CE du Conseil','Services d''agences de voyages fournis en dehors de l''UE.',''),
	 ('VATEX-EU-AE','eu','Autoliquidation','Prend en charge la règle EN 16931-1 BR-AE-10','À utiliser uniquement avec le code de catégorie TVA AE'),
	 ('VATEX-EU-D','eu','Acquisition intracommunautaire à partir de moyens de transport d''occasion','Moyens de transport d''occasion - Indication que la TVA a été acquittée conformément aux dispositions transitoires applicables','À utiliser uniquement avec le code de catégorie TVA E'),
	 ('VATEX-EU-F','eu','Acquisition intracommunautaire de biens d''occasion','Biens d''occasion - Indication que le régime de la marge bénéficiaire pour les biens d''occasion a été appliqué.','À utiliser uniquement avec le code de catégorie TVA E'),
	 ('VATEX-EU-G','eu','Exportation hors de l''UE','Conforme à la norme EN 16931-1 BR-G-10','À utiliser uniquement avec le code de catégorie TVA G'),
	 ('VATEX-EU-I','eu','Acquisition intracommunautaire d''œuvres d''art','Œuvres d''art - Indication que le régime de la marge bénéficiaire pour les œuvres d''art a été appliqué.','À utiliser uniquement avec le code de catégorie TVA E'),
	 ('VATEX-EU-IC','eu','Livraison intracommunautaire','Conforme à la norme EN 16931-1 BR-IC-10','À utiliser uniquement avec le code de catégorie TVA K'),
	 ('VATEX-EU-J','eu','Acquisition intracommunautaire d''objets de collection et d''antiquités','Objets de collection et antiquités - Indication que le régime de marge de TVA pour les objets de collection et les antiquités a été appliqué.','À utiliser uniquement avec le code de catégorie TVA E');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-EU-O','eu','Non soumis à la TVA','Conforme à la norme EN 16931-1 BR-O-10','À utiliser uniquement avec le code de catégorie TVA O'),
	 ('VATEX-FR-FRANCHISE','fr','Franchise de TVA intérieure française','Assujettis à la TVA sur le territoire national qui bénéficient d''une franchise de base (article 293 B du code général des impôts. Dans ce cas, ils sont exonérés de TVA).','Pour la facturation nationale en France'),
	 ('VATEX-FR-CNWVAT','fr','France nationale Notes de crédit sans TVA, dues au fournisseur pour la perte de TVA liée à la remise','Doctrine administrative BOI TVA DECLA 30 20 20 Â§260 et 270 pour les notes de crédit et Â§280 et 290 pour les remises','Pour les notes de crédit nationales uniquement en France'),
	 ('VATEX-FR-CGI261-1','fr','Exonération sur la base de l''article 261, paragraphe 1, du Code général des impôts (CGI)','Exonération de TVA pour les opérations soumises à d''autres taxes','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-2','fr','Exonération en vertu de l''article 261, paragraphe 2, du Code général des impôts (CGI)','Exonération de TVA pour les activités agricoles et de pêche','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-3','fr','Exonération en vertu de l''article 261, paragraphe 3, du Code général des impôts (CGI)','Exonération de TVA pour les ventes de biens d''occasion','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-4','fr','Exonération en vertu de l''article 261, paragraphe 4, du Code général des impôts (CGI)','Exonération de TVA pour les activités libérales et diverses','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-5','fr','Exonération en vertu de l''article 261, paragraphe 5, du Code général des impôts (CGI)','Exonération de TVA pour les opérations immobilières','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-7','fr','Exonération en vertu de l''article 261, paragraphe 7, du Code général des impôts (CGI)','Exonération de TVA pour les opérations des organismes à but non lucratif','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261-8','fr','Exonération en vertu de l''article 261, paragraphe 8, du Code général des impôts (CGI)','Exonération de TVA, en cas de catastrophe affectant le territoire d''un État membre de l''Union européenne, pour les livraisons de biens et les prestations de services liées à ces livraisons, lorsque l''importation de ces biens par le destinataire de ces livraisons ou par le preneur de ces prestations aurait été exonérée en vertu de l''article 291 II 2° bis du CGI','Uniquement pour la facturation nationale en France');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-FR-CGI261A','fr','Exonération en vertu de l''article 261 A du Code général des impôts (CGI)','Exonération de TVA pour les opérations effectuées par des personnes morales soumises à l''impôt sur les sociétés qui ont pour objet de céder gratuitement à leurs membres la jouissance de biens mobiliers ou immobiliers (article 239 octies du CGI)','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261B','fr','Exonération en vertu de l''article 261 B du Code général des impôts (CGI)','Exonération de TVA pour les prestations de services fournies à leurs membres par des groupements constitués de personnes physiques ou morales exerçant certaines activités exonérées de TVA sur la base des 4, à l''exception du 10°, et 7 de l''article 261 du CGI (professions et activités diverses, activités d''organismes à but non lucratif)','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261C-1','fr','Exonération sur la base du 1° de l''article 261 C du Code général des impôts (CGI)','Exonération de TVA pour les opérations bancaires et financières','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261C-2','fr','Exonération en vertu du 2° de l''article 261 C du Code général des impôts (CGI)','Exonération de TVA pour les opérations d''assurance et de réassurance et les services connexes effectués par les courtiers et intermédiaires d''assurance','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261C-3','fr','Exonération en vertu du 3° de l''article 261 C du Code général des impôts (CGI)','Exonération de TVA pour les ventes à leur valeur officielle de timbres fiscaux et de timbres-poste ayant une valeur d''affranchissement ou une valeur fiscale en France','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261D-1','fr','Exonération en vertu du 1° de l''article 261 D du Code général des impôts (CGI)','Exonération de TVA pour les locations de terrains et de bâtiments à usage agricole','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261D-1BIS','fr','Exonération en vertu du 1°bis de l''article 261 D du Code général des impôts (CGI)','Exonération de TVA pour les locations de biens immobiliers résultant d''un bail conférant un droit réel','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261D-2','fr','Exonération en vertu du 2° de l''article 261 D du Code général des impôts (CGI)','Exonération de TVA pour certaines locations de terrains non bâtis et de locaux nus, à l''exception des places de stationnement pour véhicules','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261D-3','fr','Exonération en vertu de l''article 261 D, alinéa 3, du Code général des impôts (CGI) Exonération de TVA - Article 261 D-3° du Code Général des Impôts','Exonération de TVA pour la location ou la concession de droits sur les biens visés aux 1° et 2° de l''article 261 D du CGI dans la mesure où ils concernent la gestion d''un portefeuille immobilier','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261D-4','fr','Exonération sur la base du 4° de l''article 261 D du Code général des impôts (CGI)','Exonération de TVA pour les locations occasionnelles, permanentes ou saisonnières de logements meublés ou non meublés à usage d''habitation','Uniquement pour la facturation nationale en France');
INSERT INTO public.vatex_code (vx_code,vx_country,vx_code_name,vx_description,vx_remark) VALUES
	 ('VATEX-FR-CGI261E-1','fr','Exonération en vertu du 1° de l''article 261 E du Code général des impôts (CGI)','Exonération de TVA pour l''organisation de jeux de hasard ou d''argent soumis aux prélèvements progressifs','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI261E-2','fr','Exonération en vertu du 2° de l''article 261 E du Code général des impôts (CGI)','Exonération de TVA pour les recettes provenant de l''exploitation de la loterie nationale, des paris hippiques, des paris sur les compétitions sportives et des jeux en ligne, à l''exception des rémunérations perçues par les organisateurs et les intermédiaires participant à l''organisation de ces jeux et paris','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI277A','fr','Exonération en vertu de l''article 277 A du Code général des impôts (CGI)','Régime suspensif de TVA','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI275','fr','Exonération en vertu de l''article 275 du Code général des impôts (CGI)','Achats hors TVA de biens destinés à l''exportation, à une livraison exonérée, à une livraison effectuée sur le territoire d''un autre État membre de l''Union européenne ou à une livraison hors de France, ainsi que les services liés à ces biens','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-298SEXDECIESA','fr','Exonération en vertu de l''article 298 sexdecies A du Code général des impôts (CGI)','Régime spécial applicable à l''or d''investissement','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-CGI295','fr','Exonération en vertu de l''article 295 du Code général des impôts (CGI)','Exonérations de TVA pour les territoires français visés à l''article 349 et à l''article 355, paragraphe 1, du traité sur le fonctionnement de l''Union européenne où la TVA s''applique','Uniquement pour la facturation nationale en France'),
	 ('VATEX-FR-AE','fr','Exonération en vertu de l''article 283, paragraphe 2, du Code général des impôts (CGI)','Système d''autoliquidation national','Uniquement pour la facturation nationale en France');


ALTER TABLE public.tva_rate ADD CONSTRAINT tva_rate_vatex_code_fk FOREIGN KEY (vx_code) REFERENCES public.vatex_code(vx_code);

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
          tva_peppol_code,
          vx_code
FROM tva_rate;