


ALTER TABLE ONLY public.action_comment_document
    ADD CONSTRAINT action_comment_document_pkey PRIMARY KEY (acd_id);



ALTER TABLE ONLY public.action_comment_document
    ADD CONSTRAINT action_comment_document_un UNIQUE (document_id, action_gestion_comment_id);



ALTER TABLE ONLY public.action_gestion_operation
    ADD CONSTRAINT action_comment_operation_pkey PRIMARY KEY (ago_id);



ALTER TABLE ONLY public.action_detail
    ADD CONSTRAINT action_detail_pkey PRIMARY KEY (ad_id);



ALTER TABLE ONLY public.action_gestion_comment
    ADD CONSTRAINT action_gestion_comment_pkey PRIMARY KEY (agc_id);



ALTER TABLE ONLY public.action_gestion
    ADD CONSTRAINT action_gestion_pkey PRIMARY KEY (ag_id);



ALTER TABLE ONLY public.action_gestion_related
    ADD CONSTRAINT action_gestion_related_pkey PRIMARY KEY (aga_id);



ALTER TABLE ONLY public.action_person_option
    ADD CONSTRAINT action_person_option_pk PRIMARY KEY (ap_id);



ALTER TABLE ONLY public.action_person
    ADD CONSTRAINT action_person_pkey PRIMARY KEY (ap_id);



ALTER TABLE ONLY public.action
    ADD CONSTRAINT action_pkey PRIMARY KEY (ac_id);



ALTER TABLE ONLY public.action_tags
    ADD CONSTRAINT action_tags_pkey PRIMARY KEY (at_id);



ALTER TABLE ONLY public.attr_def
    ADD CONSTRAINT attr_def_pkey PRIMARY KEY (ad_id);



ALTER TABLE ONLY public.bilan
    ADD CONSTRAINT bilan_b_name_key UNIQUE (b_name);



ALTER TABLE ONLY public.bilan
    ADD CONSTRAINT bilan_pkey PRIMARY KEY (b_id);



ALTER TABLE ONLY public.bookmark
    ADD CONSTRAINT bookmark_pkey PRIMARY KEY (b_id);



ALTER TABLE ONLY public.centralized
    ADD CONSTRAINT centralized_pkey PRIMARY KEY (c_id);



ALTER TABLE ONLY public.contact_option_ref
    ADD CONSTRAINT contact_option_ref_pk PRIMARY KEY (cor_id);



ALTER TABLE ONLY public.currency_history
    ADD CONSTRAINT currency_history_pk PRIMARY KEY (id);



ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_pk PRIMARY KEY (id);



ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_un UNIQUE (cr_code_iso);



ALTER TABLE ONLY public.del_action
    ADD CONSTRAINT del_action_pkey PRIMARY KEY (del_id);



ALTER TABLE ONLY public.del_jrn
    ADD CONSTRAINT dj_id PRIMARY KEY (dj_id);



ALTER TABLE ONLY public.del_jrnx
    ADD CONSTRAINT djx_id PRIMARY KEY (djx_id);



ALTER TABLE ONLY public.document_component
    ADD CONSTRAINT document_component_pk PRIMARY KEY (dc_id);



ALTER TABLE ONLY public.document_component
    ADD CONSTRAINT document_component_un UNIQUE (dc_code);



ALTER TABLE ONLY public.document_modele
    ADD CONSTRAINT document_modele_pkey PRIMARY KEY (md_id);



ALTER TABLE ONLY public.document_option
    ADD CONSTRAINT document_option_ref_pk PRIMARY KEY (do_id);



ALTER TABLE ONLY public.document_option
    ADD CONSTRAINT document_option_un UNIQUE (do_code, document_type_id);



ALTER TABLE ONLY public.document
    ADD CONSTRAINT document_pkey PRIMARY KEY (d_id);



ALTER TABLE ONLY public.document_state
    ADD CONSTRAINT document_state_pkey PRIMARY KEY (s_id);



ALTER TABLE ONLY public.document_type
    ADD CONSTRAINT document_type_pkey PRIMARY KEY (dt_id);



ALTER TABLE ONLY public.fiche_def
    ADD CONSTRAINT fiche_def_pkey PRIMARY KEY (fd_id);



ALTER TABLE ONLY public.fiche_def_ref
    ADD CONSTRAINT fiche_def_ref_pkey PRIMARY KEY (frd_id);



ALTER TABLE ONLY public.fiche
    ADD CONSTRAINT fiche_pkey PRIMARY KEY (f_id);



ALTER TABLE ONLY public.forecast_category
    ADD CONSTRAINT forecast_cat_pk PRIMARY KEY (fc_id);



ALTER TABLE ONLY public.forecast_item
    ADD CONSTRAINT forecast_item_pkey PRIMARY KEY (fi_id);



ALTER TABLE ONLY public.forecast
    ADD CONSTRAINT forecast_pk PRIMARY KEY (f_id);



ALTER TABLE ONLY public.form_detail
    ADD CONSTRAINT form_pkey PRIMARY KEY (fo_id);



ALTER TABLE ONLY public.form_definition
    ADD CONSTRAINT formdef_pkey PRIMARY KEY (fr_id);



ALTER TABLE ONLY public.attr_min
    ADD CONSTRAINT frd_ad_attr_min_pk PRIMARY KEY (frd_id, ad_id);



ALTER TABLE ONLY public.operation_analytique
    ADD CONSTRAINT historique_analytique_pkey PRIMARY KEY (oa_id);



ALTER TABLE ONLY public.tmp_pcmn
    ADD CONSTRAINT id_ux UNIQUE (id);



ALTER TABLE ONLY public.extension
    ADD CONSTRAINT idx_ex_code UNIQUE (ex_code);



ALTER TABLE ONLY public.info_def
    ADD CONSTRAINT info_def_pkey PRIMARY KEY (id_type);



ALTER TABLE ONLY public.jnt_document_option_contact
    ADD CONSTRAINT jnt_document_option_contact_pkey PRIMARY KEY (jdoc_id);



ALTER TABLE ONLY public.jnt_document_option_contact
    ADD CONSTRAINT jnt_document_option_contact_un UNIQUE (document_type_id, contact_option_ref_id);



ALTER TABLE ONLY public.fiche_detail
    ADD CONSTRAINT jnt_fic_att_value_pkey PRIMARY KEY (jft_id);



ALTER TABLE ONLY public.jnt_letter
    ADD CONSTRAINT jnt_letter_pk PRIMARY KEY (jl_id);



ALTER TABLE ONLY public.jnt_tag_group_tag
    ADD CONSTRAINT jnt_tag_group_tag_pkey PRIMARY KEY (jt_id);



ALTER TABLE ONLY public.jnt_tag_group_tag
    ADD CONSTRAINT jnt_tag_group_tag_un UNIQUE (tag_id, tag_group_id);



ALTER TABLE ONLY public.jrn_def
    ADD CONSTRAINT jrn_def_jrn_def_name_key UNIQUE (jrn_def_name);



ALTER TABLE ONLY public.jrn_def
    ADD CONSTRAINT jrn_def_pkey PRIMARY KEY (jrn_def_id);



ALTER TABLE ONLY public.jrn_info
    ADD CONSTRAINT jrn_info_pkey PRIMARY KEY (ji_id);



ALTER TABLE ONLY public.jrn_periode
    ADD CONSTRAINT jrn_periode_periode_ledger UNIQUE (jrn_def_id, p_id);



ALTER TABLE ONLY public.jrn_periode
    ADD CONSTRAINT jrn_periode_pk PRIMARY KEY (id);



ALTER TABLE ONLY public.jrn
    ADD CONSTRAINT jrn_pkey PRIMARY KEY (jr_id);



ALTER TABLE ONLY public.jrn_rapt
    ADD CONSTRAINT jrn_rapt_pkey PRIMARY KEY (jra_id);



ALTER TABLE ONLY public.jrn_tax
    ADD CONSTRAINT jrn_tax_pk PRIMARY KEY (jt_id);



ALTER TABLE ONLY public.jrn_type
    ADD CONSTRAINT jrn_type_pkey PRIMARY KEY (jrn_type_id);



ALTER TABLE ONLY public.jrn_note
    ADD CONSTRAINT jrnx_note_pkey PRIMARY KEY (n_id);



ALTER TABLE ONLY public.jrnx
    ADD CONSTRAINT jrnx_pkey PRIMARY KEY (j_id);



ALTER TABLE ONLY public.key_distribution_activity
    ADD CONSTRAINT key_distribution_activity_pkey PRIMARY KEY (ka_id);



ALTER TABLE ONLY public.key_distribution_detail
    ADD CONSTRAINT key_distribution_detail_pkey PRIMARY KEY (ke_id);



ALTER TABLE ONLY public.key_distribution_ledger
    ADD CONSTRAINT key_distribution_ledger_pkey PRIMARY KEY (kl_id);



ALTER TABLE ONLY public.key_distribution
    ADD CONSTRAINT key_distribution_pkey PRIMARY KEY (kd_id);



ALTER TABLE ONLY public.letter_cred
    ADD CONSTRAINT letter_cred_j_id_key UNIQUE (j_id);



ALTER TABLE ONLY public.letter_cred
    ADD CONSTRAINT letter_cred_pk PRIMARY KEY (lc_id);



ALTER TABLE ONLY public.letter_deb
    ADD CONSTRAINT letter_deb_j_id_key UNIQUE (j_id);



ALTER TABLE ONLY public.letter_deb
    ADD CONSTRAINT letter_deb_pk PRIMARY KEY (ld_id);



ALTER TABLE ONLY public.link_action_type
    ADD CONSTRAINT link_action_type_pkey PRIMARY KEY (l_id);



ALTER TABLE ONLY public.menu_default
    ADD CONSTRAINT menu_default_md_code_key UNIQUE (md_code);



ALTER TABLE ONLY public.menu_default
    ADD CONSTRAINT menu_default_pkey PRIMARY KEY (md_id);



ALTER TABLE ONLY public.menu_ref
    ADD CONSTRAINT menu_ref_pkey PRIMARY KEY (me_code);



ALTER TABLE ONLY public.payment_method
    ADD CONSTRAINT mod_payment_pkey PRIMARY KEY (mp_id);



ALTER TABLE ONLY public.op_predef
    ADD CONSTRAINT op_def_op_name_key UNIQUE (od_name, jrn_def_id);



ALTER TABLE ONLY public.op_predef
    ADD CONSTRAINT op_def_pkey PRIMARY KEY (od_id);



ALTER TABLE ONLY public.op_predef_detail
    ADD CONSTRAINT op_predef_detail_pkey PRIMARY KEY (opd_id);



ALTER TABLE ONLY public.operation_currency
    ADD CONSTRAINT operation_currency_pk PRIMARY KEY (id);



ALTER TABLE ONLY public.operation_exercice_detail
    ADD CONSTRAINT operation_exercice_detail_pkey PRIMARY KEY (oed_id);



ALTER TABLE ONLY public.operation_exercice
    ADD CONSTRAINT operation_exercice_pkey PRIMARY KEY (oe_id);



ALTER TABLE ONLY public.operation_tag
    ADD CONSTRAINT operation_tag_pkey PRIMARY KEY (opt_id);



ALTER TABLE ONLY public.parameter_extra
    ADD CONSTRAINT parameter_extra_pkey PRIMARY KEY (id);



ALTER TABLE ONLY public.parameter
    ADD CONSTRAINT parameter_pkey PRIMARY KEY (pr_id);



ALTER TABLE ONLY public.parm_appearance
    ADD CONSTRAINT parm_appearance_pkey PRIMARY KEY (a_code);



ALTER TABLE ONLY public.parm_code
    ADD CONSTRAINT parm_code_pkey PRIMARY KEY (p_code);



ALTER TABLE ONLY public.parm_money
    ADD CONSTRAINT parm_money_pkey PRIMARY KEY (pm_code);



ALTER TABLE ONLY public.parm_periode
    ADD CONSTRAINT parm_periode_pkey PRIMARY KEY (p_id);



ALTER TABLE ONLY public.parm_poste
    ADD CONSTRAINT parm_poste_pkey PRIMARY KEY (p_value);



ALTER TABLE ONLY public.parameter_extra
    ADD CONSTRAINT pe_code_ux UNIQUE (pe_code);



ALTER TABLE ONLY public.extension
    ADD CONSTRAINT pk_extension PRIMARY KEY (ex_id);



ALTER TABLE ONLY public.groupe_analytique
    ADD CONSTRAINT pk_ga_id PRIMARY KEY (ga_id);



ALTER TABLE ONLY public.jnt_fic_attr
    ADD CONSTRAINT pk_jnt_fic_attr PRIMARY KEY (jnt_id);



ALTER TABLE ONLY public.user_local_pref
    ADD CONSTRAINT pk_user_local_pref PRIMARY KEY (user_id, parameter_type);



ALTER TABLE ONLY public.plan_analytique
    ADD CONSTRAINT plan_analytique_pa_name_key UNIQUE (pa_name);



ALTER TABLE ONLY public.plan_analytique
    ADD CONSTRAINT plan_analytique_pkey PRIMARY KEY (pa_id);



ALTER TABLE ONLY public.poste_analytique
    ADD CONSTRAINT poste_analytique_pkey PRIMARY KEY (po_id);



ALTER TABLE ONLY public.profile_menu
    ADD CONSTRAINT profile_menu_pkey PRIMARY KEY (pm_id);



ALTER TABLE ONLY public.profile_menu_type
    ADD CONSTRAINT profile_menu_type_pkey PRIMARY KEY (pm_type);



ALTER TABLE ONLY public.profile_mobile
    ADD CONSTRAINT profile_mobile_code_uq UNIQUE (p_id, me_code);



ALTER TABLE ONLY public.profile_mobile
    ADD CONSTRAINT profile_mobile_pkey PRIMARY KEY (pmo_id);



ALTER TABLE ONLY public.profile
    ADD CONSTRAINT profile_pkey PRIMARY KEY (p_id);



ALTER TABLE ONLY public.profile_sec_repository
    ADD CONSTRAINT profile_sec_repository_pkey PRIMARY KEY (ur_id);



ALTER TABLE ONLY public.profile_sec_repository
    ADD CONSTRAINT profile_sec_repository_r_id_p_id_u UNIQUE (r_id, p_id);



ALTER TABLE ONLY public.profile_user
    ADD CONSTRAINT profile_user_pkey PRIMARY KEY (pu_id);



ALTER TABLE ONLY public.profile_user
    ADD CONSTRAINT profile_user_user_name_key UNIQUE (user_name, p_id);



ALTER TABLE ONLY public.quant_purchase
    ADD CONSTRAINT qp_id_pk PRIMARY KEY (qp_id);



ALTER TABLE ONLY public.quant_sold
    ADD CONSTRAINT qs_id_pk PRIMARY KEY (qs_id);



ALTER TABLE ONLY public.quant_fin
    ADD CONSTRAINT quant_fin_pk PRIMARY KEY (qf_id);



ALTER TABLE ONLY public.stock_change
    ADD CONSTRAINT stock_change_pkey PRIMARY KEY (c_id);



ALTER TABLE ONLY public.stock_goods
    ADD CONSTRAINT stock_goods_pkey PRIMARY KEY (sg_id);



ALTER TABLE ONLY public.stock_repository
    ADD CONSTRAINT stock_repository_pkey PRIMARY KEY (r_id);



ALTER TABLE ONLY public.tag_group
    ADD CONSTRAINT tag_group_pk PRIMARY KEY (tg_id);



ALTER TABLE ONLY public.operation_tag
    ADD CONSTRAINT tag_operation_uq UNIQUE (jrn_id, tag_id);



ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (t_id);



ALTER TABLE ONLY public.tmp_pcmn
    ADD CONSTRAINT tmp_pcmn_pkey PRIMARY KEY (pcm_val);



ALTER TABLE ONLY public.tmp_stockgood_detail
    ADD CONSTRAINT tmp_stockgood_detail_pkey PRIMARY KEY (d_id);



ALTER TABLE ONLY public.tmp_stockgood
    ADD CONSTRAINT tmp_stockgood_pkey PRIMARY KEY (s_id);



ALTER TABLE ONLY public.todo_list
    ADD CONSTRAINT todo_list_pkey PRIMARY KEY (tl_id);



ALTER TABLE ONLY public.todo_list_shared
    ADD CONSTRAINT todo_list_shared_pkey PRIMARY KEY (id);



ALTER TABLE ONLY public.tool_uos
    ADD CONSTRAINT tool_uos_pkey PRIMARY KEY (uos_value);



ALTER TABLE ONLY public.tva_rate
    ADD CONSTRAINT tva_code_unique UNIQUE (tva_code);



ALTER TABLE ONLY public.tva_rate
    ADD CONSTRAINT tva_id_pk PRIMARY KEY (tva_id);



ALTER TABLE ONLY public.user_sec_jrn
    ADD CONSTRAINT uniq_user_ledger UNIQUE (uj_login, uj_jrn_id);



COMMENT ON CONSTRAINT uniq_user_ledger ON public.user_sec_jrn IS 'Create an unique combination user / ledger';



ALTER TABLE ONLY public.todo_list_shared
    ADD CONSTRAINT unique_todo_list_id_login UNIQUE (todo_list_id, use_login);



ALTER TABLE ONLY public.user_active_security
    ADD CONSTRAINT user_active_security_pk PRIMARY KEY (id);



ALTER TABLE ONLY public.user_filter
    ADD CONSTRAINT user_filter_pkey PRIMARY KEY (id);



ALTER TABLE ONLY public.user_sec_act
    ADD CONSTRAINT user_sec_act_pkey PRIMARY KEY (ua_id);



ALTER TABLE ONLY public.user_sec_action_profile
    ADD CONSTRAINT user_sec_action_profile_p_id_p_granted_u UNIQUE (p_id, p_granted);



ALTER TABLE ONLY public.user_sec_action_profile
    ADD CONSTRAINT user_sec_action_profile_pkey PRIMARY KEY (ua_id);



ALTER TABLE ONLY public.user_sec_jrn
    ADD CONSTRAINT user_sec_jrn_pkey PRIMARY KEY (uj_id);



ALTER TABLE ONLY public.user_widget
    ADD CONSTRAINT user_widget_pkey PRIMARY KEY (uw_id);



ALTER TABLE ONLY public.action_gestion_related
    ADD CONSTRAINT ux_aga_least_aga_greatest UNIQUE (aga_least, aga_greatest);



ALTER TABLE ONLY public.jrn
    ADD CONSTRAINT ux_internal UNIQUE (jr_internal);



ALTER TABLE ONLY public.version
    ADD CONSTRAINT version_pkey PRIMARY KEY (val);



ALTER TABLE ONLY public.widget_dashboard
    ADD CONSTRAINT widget_dashboard_pkey PRIMARY KEY (wd_id);



ALTER TABLE ONLY public.widget_dashboard
    ADD CONSTRAINT widget_dashboard_unique UNIQUE (wd_code);



CREATE UNIQUE INDEX fd_id_ad_id_x ON public.jnt_fic_attr USING btree (fd_id, ad_id);



CREATE INDEX fiche_detail_attr_ix ON public.fiche_detail USING btree (ad_id);



CREATE UNIQUE INDEX fiche_detail_f_id_ad_id ON public.fiche_detail USING btree (f_id, ad_id);



CREATE INDEX fk_action_person_action_gestion ON public.action_person USING btree (ag_id);



CREATE INDEX fk_action_person_fiche ON public.action_person USING btree (f_id);



CREATE INDEX fk_stock_good_repository_r_id ON public.stock_goods USING btree (r_id);



CREATE INDEX fk_stock_goods_f_id ON public.stock_goods USING btree (f_id);



CREATE INDEX fk_stock_goods_j_id ON public.stock_goods USING btree (j_id);



CREATE INDEX fki_f_end_date ON public.forecast USING btree (f_end_date);



CREATE INDEX fki_f_start_date ON public.forecast USING btree (f_start_date);



CREATE INDEX fki_jrn_jr_grpt_id ON public.jrn USING btree (jr_grpt_id);



CREATE INDEX fki_jrnx_f_id ON public.jrnx USING btree (f_id);



CREATE INDEX fki_jrnx_j_grpt ON public.jrnx USING btree (j_grpt);



CREATE INDEX fki_profile_menu_me_code ON public.profile_menu USING btree (me_code);



CREATE INDEX fki_profile_menu_profile ON public.profile_menu USING btree (p_id);



CREATE INDEX fki_profile_menu_type_fkey ON public.profile_menu USING btree (p_type_display);



CREATE INDEX idx_qs_internal ON public.quant_sold USING btree (qs_internal);



CREATE INDEX jnt_fic_att_value_fd_id_idx ON public.fiche_detail USING btree (f_id);



CREATE INDEX jnt_fic_attr_fd_id_idx ON public.jnt_fic_attr USING btree (fd_id);



CREATE INDEX jrnx_j_qcode_ix ON public.jrnx USING btree (j_qcode);



CREATE UNIQUE INDEX k_ag_ref ON public.action_gestion USING btree (ag_ref);



CREATE INDEX link_action_type_fki ON public.action_gestion_related USING btree (aga_type);



CREATE UNIQUE INDEX qcode_idx ON public.fiche_detail USING btree (ad_value) WHERE (ad_id = 23);



CREATE UNIQUE INDEX qf_jr_id ON public.quant_fin USING btree (jr_id);



CREATE UNIQUE INDEX qp_j_id ON public.quant_purchase USING btree (j_id);



CREATE UNIQUE INDEX qs_j_id ON public.quant_sold USING btree (j_id);



CREATE INDEX quant_purchase_jrn_fki ON public.quant_purchase USING btree (qp_internal);



CREATE INDEX quant_sold_jrn_fki ON public.quant_sold USING btree (qs_internal);



CREATE UNIQUE INDEX uj_login_uj_jrn_id ON public.user_sec_jrn USING btree (uj_login, uj_jrn_id);



CREATE UNIQUE INDEX ux_po_name ON public.poste_analytique USING btree (po_name);



CREATE UNIQUE INDEX x_jrn_jr_id ON public.jrn USING btree (jr_id);



CREATE INDEX x_mt ON public.jrn USING btree (jr_mt);



CREATE UNIQUE INDEX x_periode ON public.parm_periode USING btree (p_start, p_end);



CREATE INDEX x_poste ON public.jrnx USING btree (j_poste);



CREATE TRIGGER action_gestion_t_insert_update BEFORE INSERT OR UPDATE ON public.action_gestion FOR EACH ROW EXECUTE FUNCTION comptaproc.action_gestion_ins_upd();



COMMENT ON TRIGGER action_gestion_t_insert_update ON public.action_gestion IS 'Truncate the column ag_title to 70 char';



CREATE TRIGGER document_modele_validate BEFORE INSERT OR UPDATE ON public.document_modele FOR EACH ROW EXECUTE FUNCTION comptaproc.t_document_modele_validate();



CREATE TRIGGER document_validate BEFORE INSERT OR UPDATE ON public.document FOR EACH ROW EXECUTE FUNCTION comptaproc.t_document_validate();



CREATE TRIGGER fiche_def_ins_upd BEFORE INSERT OR UPDATE ON public.fiche_def FOR EACH ROW EXECUTE FUNCTION comptaproc.fiche_def_ins_upd();



CREATE TRIGGER fiche_detail_check_qcode_trg BEFORE INSERT OR UPDATE ON public.fiche_detail FOR EACH ROW EXECUTE FUNCTION comptaproc.fiche_detail_check_qcode();



CREATE TRIGGER fiche_detail_check_trg BEFORE INSERT OR UPDATE ON public.fiche_detail FOR EACH ROW EXECUTE FUNCTION comptaproc.fiche_detail_check();



CREATE TRIGGER info_def_ins_upd_t BEFORE INSERT OR UPDATE ON public.info_def FOR EACH ROW EXECUTE FUNCTION comptaproc.info_def_ins_upd();



CREATE TRIGGER jrn_def_description_ins_upd BEFORE INSERT OR UPDATE ON public.jrn_def FOR EACH ROW EXECUTE FUNCTION comptaproc.t_jrn_def_description();



CREATE TRIGGER opd_limit_description BEFORE INSERT OR UPDATE ON public.op_predef FOR EACH ROW EXECUTE FUNCTION comptaproc.opd_limit_description();



CREATE TRIGGER parm_periode_check_periode_trg BEFORE INSERT OR UPDATE ON public.parm_periode FOR EACH ROW EXECUTE FUNCTION comptaproc.check_periode();



CREATE TRIGGER profile_user_ins_upd BEFORE INSERT OR UPDATE ON public.profile_user FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_profile_user_ins_upd();



COMMENT ON TRIGGER profile_user_ins_upd ON public.profile_user IS 'Force the column user_name to lowercase';



CREATE TRIGGER quant_sold_ins_upd_tr AFTER INSERT OR UPDATE ON public.quant_purchase FOR EACH ROW EXECUTE FUNCTION comptaproc.quant_purchase_ins_upd();



CREATE TRIGGER quant_sold_ins_upd_tr AFTER INSERT OR UPDATE ON public.quant_sold FOR EACH ROW EXECUTE FUNCTION comptaproc.quant_sold_ins_upd();



CREATE TRIGGER remove_action_gestion AFTER DELETE ON public.fiche FOR EACH ROW EXECUTE FUNCTION comptaproc.card_after_delete();



CREATE TRIGGER t_check_balance AFTER INSERT OR UPDATE ON public.jrn FOR EACH ROW EXECUTE FUNCTION comptaproc.proc_check_balance();



CREATE TRIGGER t_check_jrn BEFORE INSERT OR DELETE OR UPDATE ON public.jrn FOR EACH ROW EXECUTE FUNCTION comptaproc.jrn_check_periode();



CREATE TRIGGER t_code BEFORE INSERT OR UPDATE ON public.document_component FOR EACH ROW EXECUTE FUNCTION comptaproc.four_upper_letter();



CREATE TRIGGER t_group_analytic_del BEFORE DELETE ON public.groupe_analytique FOR EACH ROW EXECUTE FUNCTION comptaproc.group_analytique_del();



CREATE TRIGGER t_group_analytic_ins_upd BEFORE INSERT OR UPDATE ON public.groupe_analytique FOR EACH ROW EXECUTE FUNCTION comptaproc.group_analytic_ins_upd();



CREATE TRIGGER t_jnt_fic_attr_ins AFTER INSERT ON public.jnt_fic_attr FOR EACH ROW EXECUTE FUNCTION comptaproc.jnt_fic_attr_ins();



CREATE TRIGGER t_jrn_currency BEFORE INSERT OR UPDATE ON public.jrn FOR EACH ROW EXECUTE FUNCTION comptaproc.jrn_currency();



CREATE TRIGGER t_jrn_def_add_periode AFTER INSERT ON public.jrn_def FOR EACH ROW EXECUTE FUNCTION comptaproc.jrn_def_add();



CREATE TRIGGER t_jrn_def_delete BEFORE DELETE ON public.jrn_def FOR EACH ROW EXECUTE FUNCTION comptaproc.jrn_def_delete();



CREATE TRIGGER t_jrn_del BEFORE DELETE ON public.jrn FOR EACH ROW EXECUTE FUNCTION comptaproc.jrn_del();



CREATE TRIGGER t_jrnx_del BEFORE DELETE ON public.jrnx FOR EACH ROW EXECUTE FUNCTION comptaproc.jrnx_del();



CREATE TRIGGER t_jrnx_ins BEFORE INSERT ON public.jrnx FOR EACH ROW EXECUTE FUNCTION comptaproc.jrnx_ins();



COMMENT ON TRIGGER t_jrnx_ins ON public.jrnx IS 'check that the qcode used by the card exists and format it : uppercase and trim the space';



CREATE TRIGGER t_letter_del AFTER DELETE ON public.jrnx FOR EACH ROW EXECUTE FUNCTION comptaproc.jrnx_letter_del();



COMMENT ON TRIGGER t_letter_del ON public.jrnx IS 'Delete the lettering for this row';



CREATE TRIGGER t_plan_analytique_ins_upd BEFORE INSERT OR UPDATE ON public.plan_analytique FOR EACH ROW EXECUTE FUNCTION comptaproc.plan_analytic_ins_upd();



CREATE TRIGGER t_poste_analytique_ins_upd BEFORE INSERT OR UPDATE ON public.poste_analytique FOR EACH ROW EXECUTE FUNCTION comptaproc.poste_analytique_ins_upd();



CREATE TRIGGER t_remove_script_tag BEFORE INSERT OR UPDATE ON public.action_gestion_comment FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_remove_script_tag();



CREATE TRIGGER t_tmp_pcm_alphanum_ins_upd BEFORE INSERT OR UPDATE ON public.tmp_pcmn FOR EACH ROW EXECUTE FUNCTION comptaproc.tmp_pcmn_alphanum_ins_upd();



CREATE TRIGGER t_tmp_pcmn_ins BEFORE INSERT ON public.tmp_pcmn FOR EACH ROW EXECUTE FUNCTION comptaproc.tmp_pcmn_ins();



CREATE TRIGGER todo_list_ins_upd BEFORE INSERT OR UPDATE ON public.todo_list FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_todo_list_ins_upd();



COMMENT ON TRIGGER todo_list_ins_upd ON public.todo_list IS 'Force the column use_login to lowercase';



CREATE TRIGGER todo_list_shared_ins_upd BEFORE INSERT OR UPDATE ON public.todo_list_shared FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_todo_list_shared_ins_upd();



COMMENT ON TRIGGER todo_list_shared_ins_upd ON public.todo_list_shared IS 'Force the column ua_login to lowercase';



CREATE TRIGGER trg_action_gestion_related BEFORE INSERT OR UPDATE ON public.action_gestion_related FOR EACH ROW EXECUTE FUNCTION comptaproc.action_gestion_related_ins_up();



CREATE TRIGGER trg_category_card_before_delete BEFORE DELETE ON public.fiche_def FOR EACH ROW EXECUTE FUNCTION comptaproc.category_card_before_delete();



CREATE TRIGGER trg_extension_ins_upd BEFORE INSERT OR UPDATE ON public.extension FOR EACH ROW EXECUTE FUNCTION comptaproc.extension_ins_upd();



CREATE TRIGGER trg_set_tech_user BEFORE INSERT OR UPDATE ON public.operation_exercice FOR EACH ROW EXECUTE FUNCTION comptaproc.set_tech_user();



CREATE TRIGGER trigger_document_type_i AFTER INSERT ON public.document_type FOR EACH ROW EXECUTE FUNCTION comptaproc.t_document_type_insert();



CREATE TRIGGER trigger_jrn_def_sequence_i AFTER INSERT ON public.jrn_def FOR EACH ROW EXECUTE FUNCTION comptaproc.t_jrn_def_sequence();



CREATE TRIGGER trigger_parameter_extra_format_code_biu BEFORE INSERT OR UPDATE ON public.parameter_extra FOR EACH ROW EXECUTE FUNCTION comptaproc.t_parameter_extra_code();



CREATE TRIGGER user_sec_act_ins_upd BEFORE INSERT OR UPDATE ON public.user_sec_act FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_user_sec_act_ins_upd();



COMMENT ON TRIGGER user_sec_act_ins_upd ON public.user_sec_act IS 'Force the column ua_login to lowercase';



CREATE TRIGGER user_sec_jrn_after_ins_upd BEFORE INSERT OR UPDATE ON public.user_sec_jrn FOR EACH ROW EXECUTE FUNCTION comptaproc.trg_user_sec_jrn_ins_upd();



COMMENT ON TRIGGER user_sec_jrn_after_ins_upd ON public.user_sec_jrn IS 'Force the column uj_login to lowercase';



ALTER TABLE ONLY public.centralized
    ADD CONSTRAINT "$1" FOREIGN KEY (c_jrn_def) REFERENCES public.jrn_def(jrn_def_id);



ALTER TABLE ONLY public.user_sec_act
    ADD CONSTRAINT "$1" FOREIGN KEY (ua_act_id) REFERENCES public.action(ac_id);



ALTER TABLE ONLY public.fiche_def
    ADD CONSTRAINT "$1" FOREIGN KEY (frd_id) REFERENCES public.fiche_def_ref(frd_id);



ALTER TABLE ONLY public.attr_min
    ADD CONSTRAINT "$1" FOREIGN KEY (frd_id) REFERENCES public.fiche_def_ref(frd_id);



ALTER TABLE ONLY public.fiche
    ADD CONSTRAINT "$1" FOREIGN KEY (fd_id) REFERENCES public.fiche_def(fd_id);



ALTER TABLE ONLY public.fiche_detail
    ADD CONSTRAINT "$1" FOREIGN KEY (f_id) REFERENCES public.fiche(f_id);



ALTER TABLE ONLY public.jnt_fic_attr
    ADD CONSTRAINT "$1" FOREIGN KEY (fd_id) REFERENCES public.fiche_def(fd_id);



ALTER TABLE ONLY public.jrn
    ADD CONSTRAINT "$1" FOREIGN KEY (jr_def_id) REFERENCES public.jrn_def(jrn_def_id);



ALTER TABLE ONLY public.jrn_def
    ADD CONSTRAINT "$1" FOREIGN KEY (jrn_def_type) REFERENCES public.jrn_type(jrn_type_id);



ALTER TABLE ONLY public.jrnx
    ADD CONSTRAINT "$2" FOREIGN KEY (j_jrn_def) REFERENCES public.jrn_def(jrn_def_id);



ALTER TABLE ONLY public.attr_min
    ADD CONSTRAINT "$2" FOREIGN KEY (ad_id) REFERENCES public.attr_def(ad_id);



ALTER TABLE ONLY public.action_comment_document
    ADD CONSTRAINT action_comment_document_action_gestion_comment_id_fkey FOREIGN KEY (action_gestion_comment_id) REFERENCES public.action_gestion_comment(agc_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_comment_document
    ADD CONSTRAINT action_comment_document_document_id_fkey FOREIGN KEY (document_id) REFERENCES public.document(d_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_operation
    ADD CONSTRAINT action_comment_operation_ag_id_fkey FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_operation
    ADD CONSTRAINT action_comment_operation_jr_id_fkey FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_detail
    ADD CONSTRAINT action_detail_ag_id_fkey FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_person
    ADD CONSTRAINT action_gestion_ag_id_fk2 FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_comment
    ADD CONSTRAINT action_gestion_comment_ag_id_fkey FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_related
    ADD CONSTRAINT action_gestion_related_aga_greatest_fkey FOREIGN KEY (aga_greatest) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_related
    ADD CONSTRAINT action_gestion_related_aga_least_fkey FOREIGN KEY (aga_least) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion_related
    ADD CONSTRAINT action_gestion_related_aga_type_fkey FOREIGN KEY (aga_type) REFERENCES public.link_action_type(l_id);



ALTER TABLE ONLY public.action_person
    ADD CONSTRAINT action_person_ag_id_fkey FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_person
    ADD CONSTRAINT action_person_f_id_fkey FOREIGN KEY (f_id) REFERENCES public.fiche(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_person_option
    ADD CONSTRAINT action_person_option_fk FOREIGN KEY (action_person_id) REFERENCES public.action_person(ap_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_tags
    ADD CONSTRAINT action_tags_ag_id_fkey FOREIGN KEY (ag_id) REFERENCES public.action_gestion(ag_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_tags
    ADD CONSTRAINT action_tags_t_id_fkey FOREIGN KEY (t_id) REFERENCES public.tags(t_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_person_option
    ADD CONSTRAINT contact_option_ref_fk FOREIGN KEY (contact_option_ref_id) REFERENCES public.contact_option_ref(cor_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.currency_history
    ADD CONSTRAINT currency_history_currency_fk FOREIGN KEY (currency_id) REFERENCES public.currency(id) ON UPDATE CASCADE ON DELETE RESTRICT;



ALTER TABLE ONLY public.document_modele
    ADD CONSTRAINT document_modele_fk FOREIGN KEY (md_affect) REFERENCES public.document_component(dc_code) ON UPDATE CASCADE;



ALTER TABLE ONLY public.document_option
    ADD CONSTRAINT document_option_ref_fk FOREIGN KEY (document_type_id) REFERENCES public.document_type(dt_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.fiche_detail
    ADD CONSTRAINT fiche_detail_attr_def_fk FOREIGN KEY (ad_id) REFERENCES public.attr_def(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_person
    ADD CONSTRAINT fiche_f_id_fk2 FOREIGN KEY (f_id) REFERENCES public.fiche(f_id);



ALTER TABLE ONLY public.action_gestion
    ADD CONSTRAINT fiche_f_id_fk3 FOREIGN KEY (f_id_dest) REFERENCES public.fiche(f_id);



ALTER TABLE ONLY public.action_gestion
    ADD CONSTRAINT fk_action_gestion_document_type FOREIGN KEY (ag_type) REFERENCES public.document_type(dt_id);



ALTER TABLE ONLY public.quant_fin
    ADD CONSTRAINT fk_card FOREIGN KEY (qf_bank) REFERENCES public.fiche(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.quant_fin
    ADD CONSTRAINT fk_card_other FOREIGN KEY (qf_other) REFERENCES public.fiche(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.forecast_item
    ADD CONSTRAINT fk_forecast FOREIGN KEY (fc_id) REFERENCES public.forecast_category(fc_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_info
    ADD CONSTRAINT fk_info_def FOREIGN KEY (id_type) REFERENCES public.info_def(id_type) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_info
    ADD CONSTRAINT fk_jrn FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.quant_fin
    ADD CONSTRAINT fk_jrn FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.groupe_analytique
    ADD CONSTRAINT fk_pa_id FOREIGN KEY (pa_id) REFERENCES public.plan_analytique(pa_id) ON DELETE CASCADE;



ALTER TABLE ONLY public.jrnx
    ADD CONSTRAINT fk_pcmn_val FOREIGN KEY (j_poste) REFERENCES public.tmp_pcmn(pcm_val);



ALTER TABLE ONLY public.centralized
    ADD CONSTRAINT fk_pcmn_val FOREIGN KEY (c_poste) REFERENCES public.tmp_pcmn(pcm_val);



ALTER TABLE ONLY public.stock_goods
    ADD CONSTRAINT fk_stock_good_f_id FOREIGN KEY (f_id) REFERENCES public.fiche(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.todo_list_shared
    ADD CONSTRAINT fk_todo_list_shared_todo_list FOREIGN KEY (todo_list_id) REFERENCES public.todo_list(tl_id);



ALTER TABLE ONLY public.forecast_category
    ADD CONSTRAINT forecast_child FOREIGN KEY (f_id) REFERENCES public.forecast(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.forecast
    ADD CONSTRAINT forecast_f_end_date_fkey FOREIGN KEY (f_end_date) REFERENCES public.parm_periode(p_id) ON UPDATE SET NULL ON DELETE SET NULL;



ALTER TABLE ONLY public.forecast
    ADD CONSTRAINT forecast_f_start_date_fkey FOREIGN KEY (f_start_date) REFERENCES public.parm_periode(p_id) ON UPDATE SET NULL ON DELETE SET NULL;



ALTER TABLE ONLY public.form_detail
    ADD CONSTRAINT formdef_fk FOREIGN KEY (fo_fr_id) REFERENCES public.form_definition(fr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.letter_cred
    ADD CONSTRAINT jnt_cred_fk FOREIGN KEY (jl_id) REFERENCES public.jnt_letter(jl_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.letter_deb
    ADD CONSTRAINT jnt_deb_fk FOREIGN KEY (jl_id) REFERENCES public.jnt_letter(jl_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_document_option_contact
    ADD CONSTRAINT jnt_document_option_contact_contact_option_ref_id_fkey FOREIGN KEY (contact_option_ref_id) REFERENCES public.contact_option_ref(cor_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_document_option_contact
    ADD CONSTRAINT jnt_document_option_contact_document_type_id_fkey FOREIGN KEY (document_type_id) REFERENCES public.document_type(dt_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_fic_attr
    ADD CONSTRAINT jnt_fic_attr_attr_def_fk FOREIGN KEY (ad_id) REFERENCES public.attr_def(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_tag_group_tag
    ADD CONSTRAINT jnt_tag_group_tag_fk FOREIGN KEY (tag_id) REFERENCES public.tags(t_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_tag_group_tag
    ADD CONSTRAINT jnt_tag_group_tag_fk_1 FOREIGN KEY (tag_group_id) REFERENCES public.tag_group(tg_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn
    ADD CONSTRAINT jrn_currency_fk FOREIGN KEY (currency_id) REFERENCES public.currency(id) ON UPDATE RESTRICT ON DELETE RESTRICT;



ALTER TABLE ONLY public.jrn_def
    ADD CONSTRAINT jrn_def_currency_fk FOREIGN KEY (currency_id) REFERENCES public.currency(id);



ALTER TABLE ONLY public.op_predef
    ADD CONSTRAINT jrn_def_id_fk FOREIGN KEY (jrn_def_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_periode
    ADD CONSTRAINT jrn_per_jrn_def_id FOREIGN KEY (jrn_def_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_periode
    ADD CONSTRAINT jrn_periode_p_id FOREIGN KEY (p_id) REFERENCES public.parm_periode(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_rapt
    ADD CONSTRAINT jrn_rapt_jr_id_fkey FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_rapt
    ADD CONSTRAINT jrn_rapt_jra_concerned_fkey FOREIGN KEY (jra_concerned) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_tax
    ADD CONSTRAINT jrn_tax_acc_other_tax_fk FOREIGN KEY (ac_id) REFERENCES public.acc_other_tax(ac_id);



ALTER TABLE ONLY public.jrn_tax
    ADD CONSTRAINT jrn_tax_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrnx
    ADD CONSTRAINT jrnx_f_id_fkey FOREIGN KEY (f_id) REFERENCES public.fiche(f_id) ON UPDATE CASCADE;



ALTER TABLE ONLY public.quant_fin
    ADD CONSTRAINT jrnx_j_id_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jrn_note
    ADD CONSTRAINT jrnx_note_j_id_fkey FOREIGN KEY (jr_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_activity
    ADD CONSTRAINT key_distribution_activity_ke_id_fkey FOREIGN KEY (ke_id) REFERENCES public.key_distribution_detail(ke_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_activity
    ADD CONSTRAINT key_distribution_activity_pa_id_fkey FOREIGN KEY (pa_id) REFERENCES public.plan_analytique(pa_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_activity
    ADD CONSTRAINT key_distribution_activity_po_id_fkey FOREIGN KEY (po_id) REFERENCES public.poste_analytique(po_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_detail
    ADD CONSTRAINT key_distribution_detail_kd_id_fkey FOREIGN KEY (kd_id) REFERENCES public.key_distribution(kd_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_ledger
    ADD CONSTRAINT key_distribution_ledger_jrn_def_id_fkey FOREIGN KEY (jrn_def_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.key_distribution_ledger
    ADD CONSTRAINT key_distribution_ledger_kd_id_fkey FOREIGN KEY (kd_id) REFERENCES public.key_distribution(kd_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.letter_cred
    ADD CONSTRAINT letter_cred_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.letter_deb
    ADD CONSTRAINT letter_deb_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.document_modele
    ADD CONSTRAINT md_type FOREIGN KEY (md_type) REFERENCES public.document_type(dt_id);



ALTER TABLE ONLY public.payment_method
    ADD CONSTRAINT mod_payment_jrn_def_id_fk FOREIGN KEY (jrn_def_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.payment_method
    ADD CONSTRAINT mod_payment_mp_fd_id_fkey FOREIGN KEY (mp_fd_id) REFERENCES public.fiche_def(fd_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.payment_method
    ADD CONSTRAINT mod_payment_mp_jrn_def_id_fkey FOREIGN KEY (mp_jrn_def_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_analytique
    ADD CONSTRAINT operation_analytique_fiche_id_fk FOREIGN KEY (f_id) REFERENCES public.fiche(f_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_analytique
    ADD CONSTRAINT operation_analytique_j_id_fkey FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_analytique
    ADD CONSTRAINT operation_analytique_po_id_fkey FOREIGN KEY (po_id) REFERENCES public.poste_analytique(po_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_currency
    ADD CONSTRAINT operation_currency_jrnx_fk FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_exercice_detail
    ADD CONSTRAINT operation_exercice_detail_oe_id_fkey FOREIGN KEY (oe_id) REFERENCES public.operation_exercice(oe_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_tag
    ADD CONSTRAINT opt_jrnx FOREIGN KEY (jrn_id) REFERENCES public.jrn(jr_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.operation_tag
    ADD CONSTRAINT opt_tag_id FOREIGN KEY (tag_id) REFERENCES public.tags(t_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.poste_analytique
    ADD CONSTRAINT poste_analytique_pa_id_fkey FOREIGN KEY (pa_id) REFERENCES public.plan_analytique(pa_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.action_gestion
    ADD CONSTRAINT profile_fkey FOREIGN KEY (ag_dest) REFERENCES public.profile(p_id) ON UPDATE SET NULL ON DELETE SET NULL;



ALTER TABLE ONLY public.profile_menu
    ADD CONSTRAINT profile_menu_me_code_fkey FOREIGN KEY (me_code) REFERENCES public.menu_ref(me_code) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.profile_menu
    ADD CONSTRAINT profile_menu_p_id_fkey FOREIGN KEY (p_id) REFERENCES public.profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.profile_menu
    ADD CONSTRAINT profile_menu_type_fkey FOREIGN KEY (p_type_display) REFERENCES public.profile_menu_type(pm_type);



ALTER TABLE ONLY public.profile_mobile
    ADD CONSTRAINT profile_mobile_menu_ref_fk FOREIGN KEY (me_code) REFERENCES public.menu_ref(me_code);



ALTER TABLE ONLY public.profile_mobile
    ADD CONSTRAINT profile_mobile_profile_fk FOREIGN KEY (p_id) REFERENCES public.profile(p_id);



ALTER TABLE ONLY public.profile_sec_repository
    ADD CONSTRAINT profile_sec_repository_p_id_fkey FOREIGN KEY (p_id) REFERENCES public.profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.profile_sec_repository
    ADD CONSTRAINT profile_sec_repository_r_id_fkey FOREIGN KEY (r_id) REFERENCES public.stock_repository(r_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.profile_user
    ADD CONSTRAINT profile_user_p_id_fkey FOREIGN KEY (p_id) REFERENCES public.profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.quant_purchase
    ADD CONSTRAINT qp_vat_code_fk FOREIGN KEY (qp_vat_code) REFERENCES public.tva_rate(tva_id) ON UPDATE CASCADE;



ALTER TABLE ONLY public.quant_sold
    ADD CONSTRAINT qs_vat_code_fk FOREIGN KEY (qs_vat_code) REFERENCES public.tva_rate(tva_id) ON UPDATE CASCADE;



ALTER TABLE ONLY public.quant_purchase
    ADD CONSTRAINT quant_purchase_j_id_fkey FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.quant_purchase
    ADD CONSTRAINT quant_purchase_qp_internal_fkey FOREIGN KEY (qp_internal) REFERENCES public.jrn(jr_internal) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;



ALTER TABLE ONLY public.quant_sold
    ADD CONSTRAINT quant_sold_j_id_fkey FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.quant_sold
    ADD CONSTRAINT quant_sold_qs_internal_fkey FOREIGN KEY (qs_internal) REFERENCES public.jrn(jr_internal) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;



ALTER TABLE ONLY public.stock_change
    ADD CONSTRAINT stock_change_r_id_fkey FOREIGN KEY (r_id) REFERENCES public.stock_repository(r_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.stock_goods
    ADD CONSTRAINT stock_goods_c_id_fkey FOREIGN KEY (c_id) REFERENCES public.stock_change(c_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.stock_goods
    ADD CONSTRAINT stock_goods_j_id_fkey FOREIGN KEY (j_id) REFERENCES public.jrnx(j_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.tmp_stockgood_detail
    ADD CONSTRAINT tmp_stockgood_detail_s_id_fkey FOREIGN KEY (s_id) REFERENCES public.tmp_stockgood(s_id) ON DELETE CASCADE;



ALTER TABLE ONLY public.user_sec_jrn
    ADD CONSTRAINT uj_priv_id_fkey FOREIGN KEY (uj_jrn_id) REFERENCES public.jrn_def(jrn_def_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.user_sec_action_profile
    ADD CONSTRAINT user_sec_action_profile_p_granted_fkey FOREIGN KEY (p_granted) REFERENCES public.profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.user_sec_action_profile
    ADD CONSTRAINT user_sec_action_profile_p_id_fkey FOREIGN KEY (p_id) REFERENCES public.profile(p_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.user_widget
    ADD CONSTRAINT user_widget_dashboard_widget_id_fkey FOREIGN KEY (dashboard_widget_id) REFERENCES public.widget_dashboard(wd_id) ON UPDATE CASCADE ON DELETE CASCADE;



