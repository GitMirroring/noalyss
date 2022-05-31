begin;
create table acc_other_tax
(
    ac_id             serial        constraint acc_other_tax_pk            primary key,
    ac_label       text         not null,
    ac_rate numeric (5,2) not null,
    ajrn_def_id    integer[],
    ac_accounting account_type not null
);
comment on table acc_other_tax is 'Additional tax for Sale or Purchase ';
comment on column acc_other_tax.ac_label is 'Label of the tax';
comment on column acc_other_tax.ac_rate is 'rate of the tax in percent';
comment on column acc_other_tax.ajrn_def_id is 'array of to FK jrn_def (jrn_def_id)';
comment on column acc_other_tax.ac_accounting is 'FK tmp_pcmn (pcm_val)';

ALTER TABLE public.jrn drop CONSTRAINT jrn_pkey ;
ALTER TABLE public.jrn ADD CONSTRAINT jrn_pkey PRIMARY KEY (jr_id);

CREATE TABLE public.jrn_tax (
                                jt_id int4 NOT NULL GENERATED ALWAYS AS IDENTITY,
                                j_id int8 NOT NULL, -- fk jrnx
                                pcm_val public."account_type" NOT NULL, -- FK tmp_pcmn
                                ac_id int4 NOT NULL, -- FK to acc_other_tax
                                CONSTRAINT jrn_tax_pk PRIMARY KEY (jt_id)
);

-- Column comments

COMMENT ON COLUMN public.jrn_tax.j_id IS 'fk jrnx';
COMMENT ON COLUMN public.jrn_tax.pcm_val IS 'FK tmp_pcmn';
COMMENT ON COLUMN public.jrn_tax.ac_id IS 'FK to acc_other_tax';


-- public.jrn_tax foreign keys

ALTER TABLE public.jrn_tax ADD CONSTRAINT jrn_tax_acc_other_tax_fk FOREIGN KEY (ac_id) REFERENCES public.acc_other_tax(ac_id);
ALTER TABLE public.jrn_tax ADD CONSTRAINT jrn_tax_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id);

drop view if exists v_detail_sale;
create or replace view v_detail_sale
            (jr_id, jr_date, jr_date_paid, jr_ech, jr_tech_per, jr_comment, jr_pj_number, jr_internal, jr_def_id,
             j_poste, j_text, j_qcode, jr_rapt, item_card, item_name, qs_client, tiers_name, quick_code, tva_label,
             tva_comment, tva_both_side, vat_sided, vat_code, vat, price, quantity, price_per_unit, htva, tot_vat,
             tot_tva_np,other_tax_amount, oc_amount, oc_vat_amount, cr_code_iso)
as
WITH m AS (
    SELECT sum(quant_sold_1.qs_price)     AS htva,
           sum(quant_sold_1.qs_vat)       AS tot_vat,
           sum(quant_sold_1.qs_vat_sided) AS tot_tva_np,
           jrn_1.jr_id
    FROM quant_sold quant_sold_1
             JOIN jrnx jrnx_1 USING (j_id)
             JOIN jrn jrn_1 ON jrnx_1.j_grpt = jrn_1.jr_grpt_id
    GROUP BY jrn_1.jr_id
),other_tax as (
    select j_grpt , sum(case when j_debit is true then 0-j_montant else j_montant end) other_tax_amount from jrnx join jrn_tax using (j_id) group by j_grpt  )
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
       quant_sold.qs_fiche                          AS item_card,
       a.name                                       AS item_name,
       quant_sold.qs_client,
       b.vw_name                                    AS tiers_name,
       b.quick_code,
       tva_rate.tva_label,
       tva_rate.tva_comment,
       tva_rate.tva_both_side,
       quant_sold.qs_vat_sided                      AS vat_sided,
       quant_sold.qs_vat_code                       AS vat_code,
       quant_sold.qs_vat                            AS vat,
       quant_sold.qs_price                          AS price,
       quant_sold.qs_quantite                       AS quantity,
       quant_sold.qs_price / quant_sold.qs_quantite AS price_per_unit,
       m.htva,
       m.tot_vat,
       m.tot_tva_np,
       ot.other_tax_amount,
       oc.oc_amount,
       oc.oc_vat_amount,
       (SELECT currency.cr_code_iso
        FROM currency
        WHERE jrn.currency_id = currency.id)        AS cr_code_iso
FROM jrn
         JOIN jrnx ON jrn.jr_grpt_id = jrnx.j_grpt
         JOIN quant_sold USING (j_id)
         JOIN vw_fiche_name a ON quant_sold.qs_fiche = a.f_id
         JOIN vw_fiche_attr b ON quant_sold.qs_client = b.f_id
         LEFT JOIN tva_rate ON quant_sold.qs_vat_code = tva_rate.tva_id
         JOIN m ON m.jr_id = jrn.jr_id
         LEFT JOIN operation_currency oc ON oc.j_id = jrnx.j_id
         left join other_tax ot on ot.j_grpt=jrn.jr_grpt_id;

drop view if exists public.v_detail_purchase;

create VIEW public.v_detail_purchase
AS WITH m AS (
    SELECT sum(quant_purchase_1.qp_price) AS htva,
           sum(quant_purchase_1.qp_vat) AS tot_vat,
           sum(quant_purchase_1.qp_vat_sided) AS tot_tva_np,
           jrn_1.jr_id
    FROM quant_purchase quant_purchase_1
             JOIN jrnx jrnx_1 USING (j_id)
             JOIN jrn jrn_1 ON jrnx_1.j_grpt = jrn_1.jr_grpt_id
    GROUP BY jrn_1.jr_id
),other_tax as (
    select j_grpt , sum(case when j_debit is false then 0-j_montant else j_montant end) other_tax_amount from jrnx join jrn_tax using (j_id) group by j_grpt  )
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
          ot.other_tax_amount,
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
            LEFT JOIN tva_rate ON quant_purchase.qp_vat_code = tva_rate.tva_id
            JOIN m ON m.jr_id = jrn.jr_id
            LEFT JOIN operation_currency oc ON oc.j_id = jrnx.j_id
            left join other_tax ot on ot.j_grpt=jrn.jr_grpt_id;
INSERT INTO public.menu_ref (me_code,me_menu,me_file,me_url,me_description,me_parameter,me_javascript,me_type,me_description_etendue)
    VALUES
    ('OTAX','Autre Taxe','acc_other_tax.inc.php',NULL,'Autre Taxe pour les ventes et achats',NULL,NULL,'ME',NULL);

INSERT INTO public.profile_menu (me_code,me_code_dep,p_id,p_order,p_type_display,pm_default,pm_id_dep)
select 'OTAX','MACC',1,55,'E',0,pm_id from profile_menu where me_code='MACC';

insert into version (val,v_description) values (173,'Supplemental tax');
commit;