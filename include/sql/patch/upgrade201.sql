begin;

drop VIEW if exists public.v_quant_detail;

CREATE OR REPLACE VIEW public.v_quant_detail
AS  WITH quant AS (
    SELECT quant_purchase.j_id,
           quant_purchase.qp_fiche AS fiche_id,
           quant_purchase.qp_supplier AS tiers,
           quant_purchase.qp_vat AS vat_amount,
           quant_purchase.qp_price AS price,
           quant_purchase.qp_vat_code AS vat_code,
           quant_purchase.qp_dep_priv AS dep_priv,
           quant_purchase.qp_nd_tva AS nd_tva,
           quant_purchase.qp_nd_tva_recup AS nd_tva_recup,
           quant_purchase.qp_nd_amount AS nd_amount,
           quant_purchase.qp_vat_sided AS vat_sided
    FROM quant_purchase
    UNION ALL
    SELECT quant_sold.j_id,
           quant_sold.qs_fiche,
           quant_sold.qs_client,
           quant_sold.qs_vat,
           quant_sold.qs_price,
           quant_sold.qs_vat_code,
           0,
           0,
           0,
           0,
           quant_sold.qs_vat_sided
    FROM quant_sold
),
         sum_jrn as(
             select jrn2.jr_id,
                    quant2.tiers,
                    sum(quant2.price) AS price,
                    quant2.vat_code,
                    sum(quant2.vat_amount) AS vat_amount,
                    sum(quant2.dep_priv) AS dep_priv,
                    sum(quant2.nd_tva) AS nd_tva,
                    sum(quant2.nd_tva_recup) AS nd_tva_recup,
                    sum(quant2.nd_amount) AS nd_amount,
                    sum(quant2.vat_sided) as vat_sided
             from jrn jrn2
                      JOIN jrnx ON jrnx.j_grpt = jrn2.jr_grpt_id
                      JOIN quant quant2 USING (j_id)
             group by quant2.tiers,jrn2.jr_id,quant2.vat_code
         )
    SELECT jrn.jr_id,
           sum_jrn.tiers,
           sum_jrn.price,
           sum_jrn.vat_code,
           sum_jrn.vat_amount,
           sum_jrn.dep_priv,
           sum_jrn.nd_tva,
           sum_jrn.nd_tva_recup,
           sum_jrn.nd_amount,
           sum_jrn.vat_sided,
           jrn_def.jrn_def_name,
           jrn_def.jrn_def_type,
           vw_fiche_name.name,
           jrn.jr_comment,
           jrn.jr_montant,
           tva_rate.tva_label
    FROM jrn
             join sum_jrn on sum_jrn.jr_id=jrn.jr_id
             LEFT JOIN vw_fiche_name ON sum_jrn.tiers = vw_fiche_name.f_id
             JOIN jrn_def ON jrn_def.jrn_def_id = jrn.jr_def_id
             JOIN tva_rate ON tva_rate.tva_id = sum_jrn.vat_code
;

insert into version (val,v_description) values (202,'Bug with reconcile, improve view');
commit;
