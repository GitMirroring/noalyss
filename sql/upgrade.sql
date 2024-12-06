

alter table tva_rate add column tva_reverse_account account_type;
alter table tva_rate add constraint fk_tva_reverse_account foreign key (tva_reverse_account) references tmp_pcmn(pcm_val) on delete set null  on update cascade ;

comment on column tva_rate.tva_reverse_account is 'Accouting for reversed VAT';

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
          tva_payment_sale
   FROM tva_rate;