


ALTER TABLE ONLY public.ac_dossier
    ADD CONSTRAINT ac_dossier_pkey PRIMARY KEY (dos_id);



ALTER TABLE ONLY public.ac_users
    ADD CONSTRAINT ac_users_pkey PRIMARY KEY (use_id);



ALTER TABLE ONLY public.ac_users
    ADD CONSTRAINT ac_users_use_login_key UNIQUE (use_login);



ALTER TABLE ONLY public.audit_connect
    ADD CONSTRAINT audit_connect_pkey PRIMARY KEY (ac_id);



ALTER TABLE ONLY public.dossier_sent_email
    ADD CONSTRAINT de_date_dos_id_ux UNIQUE (de_date, dos_id);



ALTER TABLE ONLY public.dossier_sent_email
    ADD CONSTRAINT dossier_sent_email_pkey PRIMARY KEY (id);



ALTER TABLE ONLY public.jnt_use_dos
    ADD CONSTRAINT jnt_use_dos_pkey PRIMARY KEY (jnt_id);



ALTER TABLE ONLY public.modeledef
    ADD CONSTRAINT modeledef_pkey PRIMARY KEY (mod_id);



ALTER TABLE ONLY public.user_global_pref
    ADD CONSTRAINT pk_user_global_pref PRIMARY KEY (user_id, parameter_type);



ALTER TABLE ONLY public.progress
    ADD CONSTRAINT progress_pkey PRIMARY KEY (p_id);



ALTER TABLE ONLY public.recover_pass
    ADD CONSTRAINT recover_pass_pkey PRIMARY KEY (request);



ALTER TABLE ONLY public.jnt_use_dos
    ADD CONSTRAINT use_id_dos_id_uniq UNIQUE (use_id, dos_id);



ALTER TABLE ONLY public.version
    ADD CONSTRAINT version_pkey PRIMARY KEY (val);



CREATE INDEX audit_connect_ac_user ON public.audit_connect USING btree (ac_user);



CREATE INDEX fk_jnt_dos_id ON public.jnt_use_dos USING btree (dos_id);



CREATE INDEX fk_jnt_use_dos ON public.jnt_use_dos USING btree (use_id);



CREATE INDEX fki_ac_users_recover_pass_fk ON public.recover_pass USING btree (use_id);



CREATE TRIGGER limit_user_trg BEFORE INSERT OR UPDATE ON public.audit_connect FOR EACH ROW EXECUTE FUNCTION public.limit_user();



ALTER TABLE ONLY public.recover_pass
    ADD CONSTRAINT ac_users_recover_pass_fk FOREIGN KEY (use_id) REFERENCES public.ac_users(use_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.dossier_sent_email
    ADD CONSTRAINT de_ac_dossier_fk FOREIGN KEY (dos_id) REFERENCES public.ac_dossier(dos_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.user_global_pref
    ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES public.ac_users(use_login) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_use_dos
    ADD CONSTRAINT jnt_use_dos_dos_id_fkey FOREIGN KEY (dos_id) REFERENCES public.ac_dossier(dos_id) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY public.jnt_use_dos
    ADD CONSTRAINT jnt_use_dos_use_id_fkey FOREIGN KEY (use_id) REFERENCES public.ac_users(use_id);



