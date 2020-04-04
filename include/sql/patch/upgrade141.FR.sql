begin; 
update parm_poste set p_type='PAS' where p_value='40';
update tmp_pcmn set pcm_type='PAS' where pcm_val like '40%';
insert into version (val,v_description) values (142,'Bug dans PARM_POSTE');
commit ;
