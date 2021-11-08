 with sqlletter as 
             (
select
	j_id,
	jl_id
from
	letter_cred
union all
select
	j_id ,
	jl_id
from
	letter_deb )
             select
	distinct substring(jr_pj_number, '[0-9]+$'),
	j1.j_id,
	j_date,
	to_char(j_date, 'DD.MM.YYYY') as j_date_fmt,
	j_qcode,
	case
		when j_debit = 't' then j_montant
		else 0
	end as deb_montant,
	case
		when j_debit = 'f' then j_montant
		else 0
	end as cred_montant,
	jr_comment as description,
	jrn_def_name as jrn_name,
	j_poste,
	jr_pj_number,
	j_debit,
	jr_internal,
	jr_id,
	(
	select
		distinct jl_id
	from
		sqlletter
	where
		sqlletter.j_id = j1.j_id ) as letter ,
	jr_optype ,
	jr_tech_per,
	p_exercice,
	jrn_def_name,
	(with cred as (
	select
		jl_id,
		sum(j_montant) as amount_cred
	from
		letter_cred lc1
	left join jrnx as j3 on
		(j3.j_id = lc1.j_id)
	group by
		jl_id ),
	deb as (
	select
		jl_id,
		sum(j_montant) as amount_deb
	from
		letter_deb ld1
	left join jrnx as j2 on
		(j2.j_id = ld1.j_id)
	group by
		jl_id )
	select
		amount_deb-amount_cred
	from
		cred
	full join deb
			using (jl_id)
	where
		jl_id =(
		select
			distinct jl_id
		from
			sqlletter
		where
			sqlletter.j_id = j1.j_id )) as delta_letter,
	jrn_def_code,
	jrn.currency_rate,
	jrn.currency_rate_ref,
	jrn.currency_id,
	(
	select
		cr_code_iso
	from
		currency
	where
		id = jrn.currency_id) as cr_code_iso,
	j_montant,
	sum_oc_amount as oc_amount,
	sum_oc_vat_amount as oc_vat_amount,
	case when exists(select 1 from operation_analytique oa where j1.j_id=oa.j_id) then 1 else 0 end as op_analytic
from
	jrnx as j1
left join jrn_def on
	jrn_def_id = j_jrn_def
left join (
	select
		j_id,
		coalesce(oc_amount, 0) as sum_oc_amount ,
		coalesce(oc_vat_amount, 0) as sum_oc_vat_amount
	from
		jrnx
	left join operation_currency
			using (j_id)
                                              ) as v1 on
	(v1.j_id = j1.j_id )
left join jrn on
	jr_grpt_id = j_grpt
left join parm_periode on
	(p_id = jr_tech_per)
	where
	j1.j_qcode = $1
and j_date > '2020-01-01'
