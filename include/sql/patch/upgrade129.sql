begin;

CREATE OR REPLACE FUNCTION comptaproc.insert_quant_purchase(p_internal text, p_j_id numeric, p_fiche character varying, p_quant numeric, p_price numeric, p_vat numeric, p_vat_code integer, p_nd_amount numeric, p_nd_tva numeric, p_nd_tva_recup numeric, p_dep_priv numeric, p_client character varying, p_tva_sided numeric, p_price_unit numeric)
 RETURNS void
AS $function$
declare
        fid_client integer;
        fid_good   integer;
        account_priv    account_type;
        fid_good_account account_type;
        n_dep_priv numeric;
begin
        n_dep_priv := p_dep_priv;
        select p_value into account_priv from parm_code where p_code='DEP_PRIV';
        select f_id into fid_client from
                fiche_detail where ad_id=23 and ad_value=upper(trim(p_client));
        select f_id into fid_good from
                 fiche_detail where ad_id=23 and ad_value=upper(trim(p_fiche));
        select ad_value into fid_good_account from fiche_detail where ad_id=5 and f_id=fid_good;
        if strpos( fid_good_account , account_priv ) = 1 then
                n_dep_priv=p_price;
        end if; 
            
        insert into quant_purchase
                (qp_internal,
                j_id,
                qp_fiche,
                qp_quantite,
                qp_price,
                qp_vat,
                qp_vat_code,
                qp_nd_amount,
                qp_nd_tva,
                qp_nd_tva_recup,
                qp_supplier,
                qp_dep_priv,
                qp_vat_sided,
                qp_unit)
        values
                (p_internal,
                p_j_id,
                fid_good,
                p_quant,
                p_price,
                p_vat,
                p_vat_code,
                p_nd_amount,
                p_nd_tva,
                p_nd_tva_recup,
                fid_client,
                n_dep_priv,
                p_tva_sided,
                p_price_unit);
        return;
end;
$function$
LANGUAGE plpgsql;
insert into version (val,v_description) values (130,'fix bug : private fee not saved into quant_purchase');
commit;