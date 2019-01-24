begin;

drop VIEW if exists public.v_detail_sale;

CREATE OR REPLACE VIEW public.v_detail_sale as
WITH m AS (
         SELECT sum(quant_sold_1.qs_price) AS htva,
            sum(quant_sold_1.qs_vat) AS tot_vat,
            sum(quant_sold_1.qs_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM quant_sold quant_sold_1
             JOIN jrnx jrnx_1 USING (j_id)
             JOIN jrn jrn_1 ON jrnx_1.j_grpt = jrn_1.jr_grpt_id
          GROUP BY jrn_1.jr_id
        )
 SELECT jrn.jr_id,
    jrn.jr_date,
    jrn.jr_date_paid,
    jrn.jr_ech,
    jrn.jr_tech_per,
    jrn.jr_comment,
    jrn.jr_pj_number,
    jrn.jr_internal,
    jrn.jr_def_id,
    jrnx.j_poste,
    jrnx.j_text,
    jrnx.j_qcode,
    quant_sold.qs_fiche AS item_card,
    a.name AS item_name,
    quant_sold.qs_client,
    b.vw_name AS tiers_name,
    b.quick_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    tva_rate.tva_both_side,
    quant_sold.qs_vat_sided AS vat_sided,
    quant_sold.qs_vat_code AS vat_code,
    quant_sold.qs_vat AS vat,
    quant_sold.qs_price AS price,
    quant_sold.qs_quantite AS quantity,
    quant_sold.qs_price / quant_sold.qs_quantite AS price_per_unit,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    oc.oc_amount,
    oc.oc_vat_amount,
    (select cr_code_iso from currency where jrn.currency_id=currency.id) as cr_code_iso
   FROM jrn
     JOIN jrnx ON jrn.jr_grpt_id = jrnx.j_grpt
     JOIN quant_sold USING (j_id)
     JOIN vw_fiche_name a ON quant_sold.qs_fiche = a.f_id
     JOIN vw_fiche_attr b ON quant_sold.qs_client = b.f_id
     JOIN tva_rate ON quant_sold.qs_vat_code = tva_rate.tva_id
     JOIN m ON m.jr_id = jrn.jr_id
     left join operation_currency as oc on (oc.j_id=jrnx.j_id)
;

drop VIEW public.v_detail_purchase;

CREATE OR REPLACE VIEW public.v_detail_purchase
AS WITH m AS (
         SELECT sum(quant_purchase_1.qp_price) AS htva,
            sum(quant_purchase_1.qp_vat) AS tot_vat,
            sum(quant_purchase_1.qp_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM quant_purchase quant_purchase_1
             JOIN jrnx jrnx_1 USING (j_id)
             JOIN jrn jrn_1 ON jrnx_1.j_grpt = jrn_1.jr_grpt_id
          GROUP BY jrn_1.jr_id
        )
 SELECT jrn.jr_id,
    jrn.jr_date,
    jrn.jr_date_paid,
    jrn.jr_ech,
    jrn.jr_tech_per,
    jrn.jr_comment,
    jrn.jr_pj_number,
    jrn.jr_internal,
    jrn.jr_def_id,
    jrnx.j_poste,
    jrnx.j_text,
    jrnx.j_qcode,
    quant_purchase.qp_fiche AS item_card,
    a.name AS item_name,
    quant_purchase.qp_supplier,
    b.vw_name AS tiers_name,
    b.quick_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    tva_rate.tva_both_side,
    quant_purchase.qp_vat_sided AS vat_sided,
    quant_purchase.qp_vat_code AS vat_code,
    quant_purchase.qp_vat AS vat,
    quant_purchase.qp_price AS price,
    quant_purchase.qp_quantite AS quantity,
    quant_purchase.qp_price / quant_purchase.qp_quantite AS price_per_unit,
    quant_purchase.qp_nd_amount AS non_ded_amount,
    quant_purchase.qp_nd_tva AS non_ded_tva,
    quant_purchase.qp_nd_tva_recup AS non_ded_tva_recup,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    oc.oc_amount,
    oc.oc_vat_amount,
    (select cr_code_iso from currency where jrn.currency_id=currency.id) as cr_code_iso
   FROM jrn
     JOIN jrnx ON jrn.jr_grpt_id = jrnx.j_grpt
     JOIN quant_purchase USING (j_id)
     JOIN vw_fiche_name a ON quant_purchase.qp_fiche = a.f_id
     JOIN vw_fiche_attr b ON quant_purchase.qp_supplier = b.f_id
     JOIN tva_rate ON quant_purchase.qp_vat_code = tva_rate.tva_id
     JOIN m ON m.jr_id = jrn.jr_id
     left join operation_currency as oc on (oc.j_id=jrnx.j_id)
;

 create or replace view v_all_account_currency as 
select sum(oc_amount) as sum_oc_amount,sum(oc_vat_amount) as sum_oc_vat_amount,x.j_poste,x.j_id
from 
quant_fin as q1
join (select j_id ,jr_id,f_id,j_poste
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.jr_id=x.jr_id) 
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.j_poste,x.j_id
union all
select sum(oc_amount),sum(oc_vat_amount),x.j_poste,x.j_id
from 
quant_purchase as q1
join (select j_id ,jr_id,f_id,j_poste,jr_internal
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.qp_internal=x.jr_internal and (x.f_id=q1.qp_fiche or x.f_id=qp_supplier) )
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.j_poste,x.j_id
union all
select sum(oc_amount),sum(oc_vat_amount),x.j_poste,x.j_id
from 
quant_sold as q1
join (select j_id ,jr_id,f_id,j_poste,jr_internal
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.qs_internal=x.jr_internal and (x.f_id=q1.qs_fiche or x.f_id=q1.qs_client) )
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.j_poste,x.j_id
;

drop view if exists v_all_card_currency;

create or replace view v_all_card_currency as 
select sum(oc_amount) as sum_oc_amount,sum(oc_vat_amount) as sum_oc_vat_amount,x.f_id,x.j_id
from 
quant_fin as q1
join (select j_id ,jr_id,f_id,j_poste
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.jr_id=x.jr_id) 
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.f_id,x.j_id
union all
select sum(oc_amount),sum(oc_vat_amount),x.f_id,x.j_id
from 
quant_purchase as q1
join (select j_id ,jr_id,f_id,j_poste,jr_internal
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.qp_internal=x.jr_internal and (x.f_id=q1.qp_fiche or x.f_id=qp_supplier) )
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.f_id,x.j_id
union all
select sum(oc_amount),sum(oc_vat_amount),x.f_id,x.j_id
from 
quant_sold as q1
join (select j_id ,jr_id,f_id,j_poste,jr_internal
		from jrnx  as j1 join 
			 jrn as j on (j1.j_grpt=jr_grpt_id)
) as x on (q1.qs_internal=x.jr_internal and (x.f_id=q1.qs_fiche or x.f_id=q1.qs_client) )
join operation_currency as oc on (oc.j_id=q1.j_id)
group by x.f_id,x.j_id
;
insert into version (val,v_description) values (132,'Currency : Create view for managing currency ');
commit;
