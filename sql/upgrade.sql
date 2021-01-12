-- improve vw_fiche_attr
alter table fiche add column f_enable char(1);
update fiche set f_enable=ad_value from fiche_detail as fd1 where fd1.f_id=fiche.f_id and ad_id=54;
alter table fiche alter f_enable set  not null;
alter table fiche add constraint f_enable_ck  check (f_enable in ('0','1'));
comment on column fiche.f_enable is 'value = 1 if card enable , otherwise 0 ';

-- improve performance on this view
drop index if exists fiche_detail_attr_ix;
create index fiche_detail_attr_ix on fiche_detail (ad_id);

drop view vw_fiche_attr cascade;

-- add "fiche.f_enable" in the view
create view vw_fiche_attr as SELECT a.f_id,
    a.fd_id,
    a.ad_value AS vw_name,
    k.ad_value AS vw_first_name,
    b.ad_value AS vw_sell,
    c.ad_value AS vw_buy,
    d.ad_value AS tva_code,
    tva_rate.tva_id,
    tva_rate.tva_rate,
    tva_rate.tva_label,
    e.ad_value AS vw_addr,
    f.ad_value AS vw_cp,
    j.ad_value AS quick_code,
    h.ad_value AS vw_description,
    i.ad_value AS tva_num,
    fiche_def.frd_id,
    l.ad_value AS accounting,
   f_enable 
   FROM ( SELECT fiche.f_id,
            fiche.fd_id,
            fiche.f_enable,
            fiche_detail.ad_value
           FROM fiche
             LEFT JOIN fiche_detail USING (f_id)
          WHERE fiche_detail.ad_id = 1) a
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 6) b ON a.f_id = b.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 7) c ON a.f_id = c.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 2) d ON a.f_id = d.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 14) e ON a.f_id = e.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 15) f ON a.f_id = f.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 23) j ON a.f_id = j.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 9) h ON a.f_id = h.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 13) i ON a.f_id = i.f_id
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 32) k ON a.f_id = k.f_id
     LEFT JOIN tva_rate ON d.ad_value = tva_rate.tva_id::text
     JOIN fiche_def USING (fd_id)
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM fiche_detail
          WHERE fiche_detail.ad_id = 5) l ON a.f_id = l.f_id
;

create view v_detail_sale as 
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
    jrn.jr_rapt,
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
    ( SELECT currency.cr_code_iso
           FROM currency
          WHERE jrn.currency_id = currency.id) AS cr_code_iso
   FROM jrn
     JOIN jrnx ON jrn.jr_grpt_id = jrnx.j_grpt
     JOIN quant_sold USING (j_id)
     JOIN vw_fiche_name a ON quant_sold.qs_fiche = a.f_id
     JOIN vw_fiche_attr b ON quant_sold.qs_client = b.f_id
     JOIN tva_rate ON quant_sold.qs_vat_code = tva_rate.tva_id
     JOIN m ON m.jr_id = jrn.jr_id
     LEFT JOIN operation_currency oc ON oc.j_id = jrnx.j_id;
          
create view v_detail_purchase as          
 WITH m AS (
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
    jrn.jr_rapt,
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
    ( SELECT currency.cr_code_iso
           FROM currency
          WHERE jrn.currency_id = currency.id) AS cr_code_iso
   FROM jrn
     JOIN jrnx ON jrn.jr_grpt_id = jrnx.j_grpt
     JOIN quant_purchase USING (j_id)
     JOIN vw_fiche_name a ON quant_purchase.qp_fiche = a.f_id
     JOIN vw_fiche_attr b ON quant_purchase.qp_supplier = b.f_id
     JOIN tva_rate ON quant_purchase.qp_vat_code = tva_rate.tva_id
     JOIN m ON m.jr_id = jrn.jr_id
     LEFT JOIN operation_currency oc ON oc.j_id = jrnx.j_id;
          

COMMENT ON VIEW vw_fiche_attr IS 'Some attribute for all cards';
comment on view v_detail_sale is 'Summary one row by sale ';
comment on view v_detail_purchase is 'Summary one row by purchase';

-- remove 
delete from fiche_detail where ad_id=54;
delete from attr_min  where ad_id=54;
delete from attr_def where ad_id=54;
delete from jnt_fic_attr where ad_id =54;
