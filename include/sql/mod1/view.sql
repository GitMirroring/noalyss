CREATE VIEW public.v_all_account_currency AS
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_fin q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON ((q1.jr_id = x.jr_id)))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id
UNION ALL
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_purchase q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste,
            j.jr_internal
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON (((q1.qp_internal = x.jr_internal) AND ((x.f_id = q1.qp_fiche) OR (x.f_id = q1.qp_supplier)))))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id
UNION ALL
 SELECT sum(oc.oc_amount) AS sum_oc_amount,
    sum(oc.oc_vat_amount) AS sum_oc_vat_amount,
    x.j_poste,
    x.j_id
   FROM ((public.quant_sold q1
     JOIN ( SELECT j1.j_id,
            j.jr_id,
            j1.f_id,
            j1.j_poste,
            j.jr_internal
           FROM (public.jrnx j1
             JOIN public.jrn j ON ((j1.j_grpt = j.jr_grpt_id)))) x ON (((q1.qs_internal = x.jr_internal) AND ((x.f_id = q1.qs_fiche) OR (x.f_id = q1.qs_client)))))
     JOIN public.operation_currency oc ON ((oc.j_id = q1.j_id)))
  GROUP BY x.j_poste, x.j_id;
CREATE VIEW public.v_all_card_currency AS
 SELECT sum(operation_currency.oc_amount) AS sum_oc_amount,
    sum(operation_currency.oc_vat_amount) AS sum_oc_vat_amount,
    jrnx.f_id,
    operation_currency.j_id
   FROM (public.operation_currency
     JOIN public.jrnx USING (j_id))
  GROUP BY jrnx.f_id, operation_currency.j_id;
CREATE VIEW public.v_all_menu AS
 SELECT pm.me_code,
    pm.pm_id,
    pm.me_code_dep,
    pm.p_order,
    pm.p_type_display,
    p.p_name,
    p.p_desc,
    mr.me_menu,
    mr.me_file,
    mr.me_url,
    mr.me_parameter,
    mr.me_javascript,
    mr.me_type,
    pm.p_id,
    mr.me_description
   FROM ((public.profile_menu pm
     JOIN public.profile p ON ((p.p_id = pm.p_id)))
     JOIN public.menu_ref mr USING (me_code))
  ORDER BY pm.p_order;
CREATE VIEW public.v_contact AS
 WITH contact_data AS (
         SELECT f.f_id,
            f.f_enable,
            f.fd_id
           FROM (public.fiche f
             JOIN public.fiche_def fd ON ((f.fd_id = fd.fd_id)))
          WHERE (fd.frd_id = 16)
        )
 SELECT cd.f_id,
    cd.f_enable,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 32) AND (fiche_detail.f_id = cd.f_id))) AS contact_fname,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 1) AND (fiche_detail.f_id = cd.f_id))) AS contact_name,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 23) AND (fiche_detail.f_id = cd.f_id))) AS contact_qcode,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 25) AND (fiche_detail.f_id = cd.f_id))) AS contact_company,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 27) AND (fiche_detail.f_id = cd.f_id))) AS contact_mobile,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 17) AND (fiche_detail.f_id = cd.f_id))) AS contact_phone,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 18) AND (fiche_detail.f_id = cd.f_id))) AS contact_email,
    ( SELECT fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE ((fiche_detail.ad_id = 26) AND (fiche_detail.f_id = cd.f_id))) AS contact_fax,
    cd.fd_id AS card_category
   FROM contact_data cd;
CREATE VIEW public.v_currency_last_value AS
 WITH recent_rate AS (
         SELECT currency_history.currency_id,
            max(currency_history.ch_from) AS rc_from
           FROM public.currency_history
          GROUP BY currency_history.currency_id
        )
 SELECT cr1.id AS currency_id,
    cr1.cr_name,
    cr1.cr_code_iso,
    ch1.id AS currency_history_id,
    ch1.ch_value,
    to_char((recent_rate.rc_from)::timestamp with time zone, 'DD.MM.YYYY'::text) AS str_from
   FROM ((public.currency cr1
     JOIN recent_rate ON ((recent_rate.currency_id = cr1.id)))
     JOIN public.currency_history ch1 ON (((recent_rate.currency_id = ch1.currency_id) AND (recent_rate.rc_from = ch1.ch_from))));
CREATE VIEW public.vw_fiche_attr AS
 SELECT a.f_id,
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
    a.f_enable
   FROM ((((((((((((( SELECT fiche.f_id,
            fiche.fd_id,
            fiche.f_enable,
            fiche_detail.ad_value
           FROM (public.fiche
             LEFT JOIN public.fiche_detail USING (f_id))
          WHERE (fiche_detail.ad_id = 1)) a
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 6)) b ON ((a.f_id = b.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 7)) c ON ((a.f_id = c.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 2)) d ON ((a.f_id = d.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) e ON ((a.f_id = e.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) f ON ((a.f_id = f.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) j ON ((a.f_id = j.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 9)) h ON ((a.f_id = h.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) i ON ((a.f_id = i.f_id)))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 32)) k ON ((a.f_id = k.f_id)))
     LEFT JOIN public.tva_rate ON ((d.ad_value = (tva_rate.tva_id)::text)))
     JOIN public.fiche_def USING (fd_id))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) l ON ((a.f_id = l.f_id)));
CREATE VIEW public.vw_fiche_name AS
 SELECT fiche_detail.f_id,
    fiche_detail.ad_value AS name
   FROM public.fiche_detail
  WHERE (fiche_detail.ad_id = 1);
CREATE VIEW public.v_detail_purchase AS
 WITH m AS (
         SELECT sum(quant_purchase_1.qp_price) AS htva,
            sum(quant_purchase_1.qp_vat) AS tot_vat,
            sum(quant_purchase_1.qp_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM ((public.quant_purchase quant_purchase_1
             JOIN public.jrnx jrnx_1 USING (j_id))
             JOIN public.jrn jrn_1 ON ((jrnx_1.j_grpt = jrn_1.jr_grpt_id)))
          GROUP BY jrn_1.jr_id
        ), other_tax AS (
         SELECT jrnx_1.j_grpt,
            sum(
                CASE
                    WHEN (jrnx_1.j_debit IS FALSE) THEN ((0)::numeric - jrnx_1.j_montant)
                    ELSE jrnx_1.j_montant
                END) AS other_tax_amount
           FROM (public.jrnx jrnx_1
             JOIN public.jrn_tax USING (j_id))
          GROUP BY jrnx_1.j_grpt
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
    (quant_purchase.qp_price / quant_purchase.qp_quantite) AS price_per_unit,
    quant_purchase.qp_nd_amount AS non_ded_amount,
    quant_purchase.qp_nd_tva AS non_ded_tva,
    quant_purchase.qp_nd_tva_recup AS non_ded_tva_recup,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    ot.other_tax_amount,
    oc.oc_amount,
    oc.oc_vat_amount,
    ( SELECT currency.cr_code_iso
           FROM public.currency
          WHERE (jrn.currency_id = currency.id)) AS cr_code_iso
   FROM ((((((((public.jrn
     JOIN public.jrnx ON ((jrn.jr_grpt_id = jrnx.j_grpt)))
     JOIN public.quant_purchase USING (j_id))
     JOIN public.vw_fiche_name a ON ((quant_purchase.qp_fiche = a.f_id)))
     JOIN public.vw_fiche_attr b ON ((quant_purchase.qp_supplier = b.f_id)))
     LEFT JOIN public.tva_rate ON ((quant_purchase.qp_vat_code = tva_rate.tva_id)))
     JOIN m ON ((m.jr_id = jrn.jr_id)))
     LEFT JOIN public.operation_currency oc ON ((oc.j_id = jrnx.j_id)))
     LEFT JOIN other_tax ot ON ((ot.j_grpt = jrn.jr_grpt_id)));
CREATE VIEW public.v_detail_sale AS
 WITH m AS (
         SELECT sum(quant_sold_1.qs_price) AS htva,
            sum(quant_sold_1.qs_vat) AS tot_vat,
            sum(quant_sold_1.qs_vat_sided) AS tot_tva_np,
            jrn_1.jr_id
           FROM ((public.quant_sold quant_sold_1
             JOIN public.jrnx jrnx_1 USING (j_id))
             JOIN public.jrn jrn_1 ON ((jrnx_1.j_grpt = jrn_1.jr_grpt_id)))
          GROUP BY jrn_1.jr_id
        ), other_tax AS (
         SELECT jrnx_1.j_grpt,
            sum(
                CASE
                    WHEN (jrnx_1.j_debit IS TRUE) THEN ((0)::numeric - jrnx_1.j_montant)
                    ELSE jrnx_1.j_montant
                END) AS other_tax_amount
           FROM (public.jrnx jrnx_1
             JOIN public.jrn_tax USING (j_id))
          GROUP BY jrnx_1.j_grpt
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
    (quant_sold.qs_price / quant_sold.qs_quantite) AS price_per_unit,
    m.htva,
    m.tot_vat,
    m.tot_tva_np,
    ot.other_tax_amount,
    oc.oc_amount,
    oc.oc_vat_amount,
    ( SELECT currency.cr_code_iso
           FROM public.currency
          WHERE (jrn.currency_id = currency.id)) AS cr_code_iso
   FROM ((((((((public.jrn
     JOIN public.jrnx ON ((jrn.jr_grpt_id = jrnx.j_grpt)))
     JOIN public.quant_sold USING (j_id))
     JOIN public.vw_fiche_name a ON ((quant_sold.qs_fiche = a.f_id)))
     JOIN public.vw_fiche_attr b ON ((quant_sold.qs_client = b.f_id)))
     LEFT JOIN public.tva_rate ON ((quant_sold.qs_vat_code = tva_rate.tva_id)))
     JOIN m ON ((m.jr_id = jrn.jr_id)))
     LEFT JOIN public.operation_currency oc ON ((oc.j_id = jrnx.j_id)))
     LEFT JOIN other_tax ot ON ((ot.j_grpt = jrn.jr_grpt_id)));
CREATE VIEW public.v_menu_dependency AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue,
            p.p_id
           FROM ((public.profile_menu pm
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.pm_id,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (v2.pm_id IS NOT NULL) THEN v2.pm_id
            WHEN (v3.pm_id IS NOT NULL) THEN v3.pm_id
            ELSE NULL::integer
        END AS higher_dep,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript,
    v1.p_id,
    v2.p_id AS v2pid,
    v3.p_id AS v3pid
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE ((COALESCE(v2.p_id, v1.p_id) = v1.p_id) AND (COALESCE(v3.p_id, v1.p_id) = v1.p_id) AND (v1.p_type_display <> 'P'::text))
  ORDER BY v1.pm_id;
CREATE VIEW public.v_menu_description AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            pm.pm_id_dep,
            pm.p_id,
            mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pu.user_name,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue
           FROM (((public.profile_menu pm
             JOIN public.profile_user pu ON ((pu.p_id = pm.p_id)))
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    v1.user_name,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript,
    v1.pm_id,
    v1.pm_id_dep,
    v1.p_id
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE ((v1.p_type_display <> 'P'::text) AND ((COALESCE(v1.me_file, ''::text) <> ''::text) OR (COALESCE(v1.me_javascript, ''::text) <> ''::text)));
CREATE VIEW public.v_menu_description_favori AS
 WITH t_menu AS (
         SELECT mr.me_menu,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pu.user_name,
            mr.me_file,
            mr.me_javascript,
            mr.me_description,
            mr.me_description_etendue
           FROM (((public.profile_menu pm
             JOIN public.profile_user pu ON ((pu.p_id = pm.p_id)))
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
             JOIN public.menu_ref mr USING (me_code))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v1.me_code,
    v1.me_description,
    v1.me_description_etendue,
    v1.me_file,
    v1.user_name,
    ('> '::text || v1.me_menu) AS v1menu,
        CASE
            WHEN (COALESCE(v3.me_menu, ''::text) <> ''::text) THEN (' > '::text || v2.me_menu)
            ELSE v2.me_menu
        END AS v2menu,
    v3.me_menu AS v3menu,
    v3.p_type_display,
    COALESCE(v1.me_javascript, COALESCE(v2.me_javascript, v3.me_javascript)) AS javascript
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.me_code_dep = v2.me_code)))
     LEFT JOIN t_menu v3 ON ((v2.me_code_dep = v3.me_code)))
  WHERE (v1.p_type_display <> 'P'::text);
CREATE VIEW public.v_menu_profile AS
 WITH t_menu AS (
         SELECT pm.pm_id,
            pm.pm_id_dep,
            pm.me_code,
            pm.me_code_dep,
            pm.p_type_display,
            pm.p_id
           FROM (public.profile_menu pm
             JOIN public.profile p ON ((p.p_id = pm.p_id)))
        )
 SELECT DISTINCT ((COALESCE((v3.me_code || '/'::text), ''::text) || COALESCE(v2.me_code, ''::text)) ||
        CASE
            WHEN (v2.me_code IS NULL) THEN COALESCE(v1.me_code, ''::text)
            WHEN (v2.me_code IS NOT NULL) THEN COALESCE(('/'::text || v1.me_code), ''::text)
            ELSE NULL::text
        END) AS code,
    v3.p_type_display,
    COALESCE(v3.pm_id, 0) AS pm_id_v3,
    COALESCE(v2.pm_id, 0) AS pm_id_v2,
    v1.pm_id AS pm_id_v1,
    v1.p_id
   FROM ((t_menu v1
     LEFT JOIN t_menu v2 ON ((v1.pm_id_dep = v2.pm_id)))
     LEFT JOIN t_menu v3 ON ((v2.pm_id_dep = v3.pm_id)))
  WHERE (v1.p_type_display <> 'P'::text);
CREATE VIEW public.v_quant_detail AS
 WITH quant AS (
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
           FROM public.quant_purchase
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
           FROM public.quant_sold
        )
 SELECT jrn.jr_id,
    quant.tiers,
    jrn_def.jrn_def_name,
    jrn_def.jrn_def_type,
    vw_fiche_name.name,
    jrn.jr_comment,
    jrn.jr_montant,
    sum(quant.price) AS price,
    quant.vat_code,
    sum(quant.vat_amount) AS vat_amount,
    sum(quant.dep_priv) AS dep_priv,
    sum(quant.nd_tva) AS nd_tva,
    sum(quant.nd_tva_recup) AS nd_tva_recup,
    sum(quant.nd_amount) AS nd_amount,
    quant.vat_sided,
    tva_rate.tva_label
   FROM (((((public.jrn
     JOIN public.jrnx ON ((jrnx.j_grpt = jrn.jr_grpt_id)))
     JOIN quant USING (j_id))
     LEFT JOIN public.vw_fiche_name ON ((quant.tiers = vw_fiche_name.f_id)))
     JOIN public.jrn_def ON ((jrn_def.jrn_def_id = jrn.jr_def_id)))
     JOIN public.tva_rate ON ((tva_rate.tva_id = quant.vat_code)))
  GROUP BY jrn.jr_id, quant.tiers, jrn.jr_comment, jrn.jr_montant, quant.vat_code, quant.vat_sided, vw_fiche_name.name, jrn_def.jrn_def_name, jrn_def.jrn_def_type, tva_rate.tva_label;
CREATE VIEW public.v_tva_rate AS
 SELECT tva_rate.tva_id,
    tva_rate.tva_rate,
    tva_rate.tva_code,
    tva_rate.tva_label,
    tva_rate.tva_comment,
    split_part(tva_rate.tva_poste, ','::text, 1) AS tva_purchase,
    split_part(tva_rate.tva_poste, ','::text, 2) AS tva_sale,
    tva_rate.tva_both_side,
    tva_rate.tva_payment_purchase,
    tva_rate.tva_payment_sale
   FROM public.tva_rate;
CREATE VIEW public.vw_client AS
 SELECT fiche.f_id,
    a1.ad_value AS name,
    a.ad_value AS quick_code,
    b.ad_value AS tva_num,
    c.ad_value AS poste_comptable,
    d.ad_value AS rue,
    e.ad_value AS code_postal,
    f.ad_value AS pays,
    g.ad_value AS telephone,
    h.ad_value AS email
   FROM (((((((((((public.fiche
     JOIN public.fiche_def USING (fd_id))
     JOIN public.fiche_def_ref USING (frd_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 1)) a1 USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) b USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) c USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) d USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) e USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 16)) f USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 17)) g USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 18)) h USING (f_id))
  WHERE (fiche_def_ref.frd_id = 9);
CREATE VIEW public.vw_fiche_def AS
 SELECT jnt_fic_attr.fd_id,
    jnt_fic_attr.ad_id,
    attr_def.ad_text,
    fiche_def.fd_class_base,
    fiche_def.fd_label,
    fiche_def.fd_create_account,
    fiche_def.frd_id
   FROM ((public.fiche_def
     JOIN public.jnt_fic_attr USING (fd_id))
     JOIN public.attr_def ON ((attr_def.ad_id = jnt_fic_attr.ad_id)));
CREATE VIEW public.vw_fiche_min AS
 SELECT attr_min.frd_id,
    attr_min.ad_id,
    attr_def.ad_text,
    fiche_def_ref.frd_text,
    fiche_def_ref.frd_class_base
   FROM ((public.attr_min
     JOIN public.attr_def USING (ad_id))
     JOIN public.fiche_def_ref USING (frd_id));
CREATE VIEW public.vw_poste_qcode AS
 SELECT c.f_id,
    a.ad_value AS j_poste,
    b.ad_value AS j_qcode
   FROM ((public.fiche c
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.f_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) b USING (f_id));
CREATE VIEW public.vw_supplier AS
 SELECT fiche.f_id,
    a1.ad_value AS name,
    a.ad_value AS quick_code,
    b.ad_value AS tva_num,
    c.ad_value AS poste_comptable,
    d.ad_value AS rue,
    e.ad_value AS code_postal,
    f.ad_value AS pays,
    g.ad_value AS telephone,
    h.ad_value AS email
   FROM (((((((((((public.fiche
     JOIN public.fiche_def USING (fd_id))
     JOIN public.fiche_def_ref USING (frd_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 1)) a1 USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 13)) b USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 23)) a USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 5)) c USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 14)) d USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 15)) e USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 16)) f USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 17)) g USING (f_id))
     LEFT JOIN ( SELECT fiche_detail.jft_id,
            fiche_detail.f_id,
            fiche_detail.ad_id,
            fiche_detail.ad_value
           FROM public.fiche_detail
          WHERE (fiche_detail.ad_id = 18)) h USING (f_id))
  WHERE (fiche_def_ref.frd_id = 8);
