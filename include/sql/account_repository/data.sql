set search_path = public,comptaproc,pg_catalog ;


SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;




INSERT INTO public.ac_users (use_id, use_first_name, use_name, use_login, use_active, use_pass, use_admin, use_email) VALUES (4, 'demo', 'demo', 'demo', 0, 'fe01ce2a7fbac8fafaed7c982a04e229', 0, NULL);
INSERT INTO public.ac_users (use_id, use_first_name, use_name, use_login, use_active, use_pass, use_admin, use_email) VALUES (1, NULL, NULL, 'admin', 1, '04cd7fc709122459e51c439171c43209', 1, NULL);












INSERT INTO public.modeledef (mod_id, mod_name, mod_desc) VALUES (1, '(BE) Basique', 'Comptabilité Belge, à adapter');
INSERT INTO public.modeledef (mod_id, mod_name, mod_desc) VALUES (2, '(FR) Basique', 'Comptabilité Française, à adapter');









INSERT INTO public.theme (the_name, the_filestyle, the_filebutton) VALUES ('Light', 'style-light.css', NULL);
INSERT INTO public.theme (the_name, the_filestyle, the_filebutton) VALUES ('Classique', 'style-classic.css', NULL);
INSERT INTO public.theme (the_name, the_filestyle, the_filebutton) VALUES ('Classic7', 'style-classic7.css', NULL);



INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('demo', 'PAGESIZE', '50');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('demo', 'LANG', 'fr_FR.utf8');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('admin', 'PAGESIZE', '50');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('admin', 'LANG', 'fr_FR.utf8');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('admin', 'TOPMENU', 'TEXT');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('demo', 'THEME', 'Classic7');
INSERT INTO public.user_global_pref (user_id, parameter_type, parameter_value) VALUES ('admin', 'THEME', 'Classic7');



INSERT INTO public.version (val) VALUES (18);



SELECT pg_catalog.setval('public.audit_connect_ac_id_seq', 1, false);



SELECT pg_catalog.setval('public.dossier_id', 24, true);



SELECT pg_catalog.setval('public.dossier_sent_email_id_seq', 1, false);



SELECT pg_catalog.setval('public.s_modid', 8, true);



SELECT pg_catalog.setval('public.seq_jnt_use_dos', 28, true);



SELECT pg_catalog.setval('public.seq_priv_user', 12, true);



SELECT pg_catalog.setval('public.users_id', 5, true);



