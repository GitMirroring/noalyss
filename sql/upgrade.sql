alter table action_gestion drop ag_ref_ag_id;
--- repository insert into theme (the_name,the_filestyle) values ('Classic 692','style-r692.css');

create sequence tmp_pcmn_id_seq;
ALTER TABLE tmp_pcmn ADD COLUMN id bigint;
update tmp_pcmn set id=nextval('tmp_pcmn_id_seq');

ALTER TABLE tmp_pcmn ALTER COLUMN id SET NOT NULL;
ALTER TABLE tmp_pcmn ALTER COLUMN id SET DEFAULT nextval('tmp_pcmn_id_seq'::regclass);
ALTER TABLE tmp_pcmn   ADD CONSTRAINT id_ux UNIQUE(id);
COMMENT ON COLUMN tmp_pcmn.id IS 'allow to identify the row, it is unique and not null (pseudo pk)';


insert into bilan (b_name,b_file_template,b_file_form,b_type) values ('ASBL','document/fr_be/bnb-asbl.rtf','document/fr_be/bnb-asbl.form','RTF');

alter table jnt_letter drop jl_amount_deb;

ALTER TABLE operation_analytique ADD COLUMN f_id bigint;
ALTER TABLE operation_analytique  ADD CONSTRAINT operation_analytique_fiche_id_fk FOREIGN KEY (f_id)       REFERENCES fiche (f_id) MATCH SIMPLE       ON UPDATE cascade ON cascade;
COMMENT ON COLUMN operation_analytique.f_id IS 'FK to fiche.f_id , used only with ODS';

CREATE OR REPLACE FUNCTION comptaproc.table_analytic_account(p_from text, p_to text)
 RETURNS SETOF anc_table_account_type
 LANGUAGE plpgsql
AS $function$
declare
	ret ANC_table_account_type%ROWTYPE;
	sql_from text:='';
	sql_to text:='';
	sWhere text:='';
	sAnd text:='';
	sResult text:='';
begin
if p_from <> '' and p_from is not null then
	sql_from:='oa_date >= to_date('''||p_from::text||''',''DD.MM.YYYY'')';
	sWhere:=' where ';
end if;

if p_to <> '' and p_to is not null then
	sql_to=' oa_date <= to_date('''||p_to::text||''',''DD.MM.YYYY'')';
	sWhere := ' where ';
end if;

if sql_to <> '' and sql_from <> '' then
	sAnd:=' and ';
end if;

sResult := sWhere || sql_from || sAnd || sql_to;

for ret in EXECUTE '
SELECT po.po_id,
			    po.pa_id, po.po_name, 
			    po.po_description,sum(
        CASE
            WHEN oa1.oa_debit = true THEN oa1.oa_amount * (-1)::numeric
            ELSE oa1.oa_amount
        END) AS sum_amount, coalesce(jrnx.j_poste,fd1.ad_value) as j_poste, tmp_pcmn.pcm_lib AS name
   FROM operation_analytique as oa1
   JOIN poste_analytique po USING (po_id)
   left join fiche_detail as fd1 on (oa1.f_id=fd1.f_id and fd1.ad_id=5)
   left JOIN jrnx USING (j_id)
   join tmp_pcmn ON (jrnx.j_poste::text = tmp_pcmn.pcm_val::text or tmp_pcmn.pcm_val=fd1.ad_value)
'|| sResult ||'
  GROUP BY po.po_id, po.po_name, po.pa_id, coalesce(jrnx.j_poste,fd1.ad_value), tmp_pcmn.pcm_lib, po.po_description
 HAVING sum(
CASE
    WHEN oa1.oa_debit = true THEN oa1.oa_amount * (-1)::numeric
    ELSE oa1.oa_amount
END) <> 0::numeric 
'
	loop
	return next ret;
end loop;
end;
$function$
;


CREATE OR REPLACE FUNCTION comptaproc.table_analytic_card(p_from text, p_to text)
 RETURNS SETOF anc_table_card_type
 LANGUAGE plpgsql
AS $function$
declare
	ret ANC_table_card_type%ROWTYPE;
	sql_from text:='';
	sql_to text:='';
	sWhere text:='';
	sAnd text:='';
	sResult text:='';
begin
if p_from <> '' and p_from is not null then
	sql_from:='oa_date >= to_date('''||p_from::text||''',''DD.MM.YYYY'')';
	sWhere:=' where ';
end if;

if p_to <> '' and p_to is not null then
	sql_to=' oa_date <= to_date('''||p_to::text||''',''DD.MM.YYYY'')';
	sWhere := ' where ';
end if;

if sql_to <> '' and sql_from <> '' then
	sAnd :=' and ';
end if;

sResult := sWhere || sql_from || sAnd || sql_to;

for ret in EXECUTE ' 
with m as (select po_id,
 		coalesce(jrnx.f_id,operation_analytique.f_id) as f_id1,
		case when jrnx.j_qcode is not null then 
        ( SELECT fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 1 AND fiche_detail.f_id = jrnx.f_id) 
          when jrnx.f_id is null and operation_analytique.f_id is not null then 
          ( SELECT fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 1 AND fiche_detail.f_id = operation_analytique.f_id)
         end
          AS name,
           case when jrnx.j_qcode is not null then
        jrnx.j_qcode
        when jrnx.f_id is null then
        (SELECT fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 23 AND fiche_detail.f_id = operation_analytique.f_id) end as j_qcode
   FROM operation_analytique
   left JOIN jrnx USING (j_id) ) 
SELECT po.po_id, po.pa_id, po.po_name, po.po_description,  sum(
        CASE
            WHEN operation_analytique.oa_debit = true THEN operation_analytique.oa_amount * (-1)::numeric
            ELSE operation_analytique.oa_amount
        END) AS sum_amount,
 		m.f_id1, 
        m.name,
        m.j_qcode
   FROM operation_analytique
   JOIN poste_analytique po USING (po_id)
   join m using (po_id)
'|| sResult ||'
  GROUP BY po.po_id, po.po_name, po.pa_id, m.f_id1, m.j_qcode,m.name, po.po_description
 HAVING sum(
CASE
    WHEN operation_analytique.oa_debit = true THEN operation_analytique.oa_amount * (-1)::numeric
    ELSE operation_analytique.oa_amount
END) <> 0::numeric'
	loop
	return next ret;
end loop;
end;
$function$
;