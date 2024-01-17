begin;

CREATE OR REPLACE FUNCTION comptaproc.get_follow_up_tree(action_gestion_id integer)
    RETURNS SETOF integer

    LANGUAGE plpgsql
AS $function$
declare
    i int;
    x int;
    e int;
begin
    for x in select aga_least
             from action_gestion_related
             where
                 aga_greatest = action_gestion_id
        loop
            return next x;

            for e in select *  from  comptaproc.get_follow_up_tree(x)
                loop
                    return next e;
                end loop;

        end loop;
    return;
end;
$function$;

insert into version (val,v_description) values (192,'2323 : tree for depending event');
commit;