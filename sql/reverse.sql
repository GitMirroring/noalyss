
create trigger t_jrnx_upd before update on
    public.jrnx for each row execute function jrnx_ins();