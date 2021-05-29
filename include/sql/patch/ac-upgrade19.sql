begin;

update user_global_pref set parameter_value='Classic7' where parameter_type='THEME';
delete from theme where the_name != 'Classic7';


select upgrade_repo(20);
commit;