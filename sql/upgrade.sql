
update menu_ref set me_menu='Navigateur &#x1F9ED;' where me_code ~ 'NAVI';

update menu_ref set me_menu='Configuration &#x1F527;' where me_code='CFG';


ALTER TABLE public.tva_rate ADD CONSTRAINT tva_code_number_check CHECK (tva_code::text !~ '^([0-9]+)$');

-- drop view if exists  public.vw_fiche_attr;

CREATE OR REPLACE VIEW public.vw_fiche_attr
AS SELECT a.f_id,
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
                        WHERE fiche_detail.ad_id = 5) l ON a.f_id = l.f_id;

COMMENT ON VIEW public.vw_fiche_attr IS 'Some attribute for all cards';
