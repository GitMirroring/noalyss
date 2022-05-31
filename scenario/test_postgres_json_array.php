<?php
//@description: test pour PostgreSQL JSON & ARRAY
$cnx=$cn->get_db();

$ret = pg_query($cnx,"select * from jrn_def ");

$result=pg_fetch_all($ret);

echo  "retrieve ",count($result);

$ret=pg_query($cnx,"drop table if exists public.test_array_int");

$ret = pg_query ($cnx,
"CREATE TABLE public.test_array_int(
    ac_id serial4 NOT NULL PRIMARY KEY ,
	ac_label text NOT NULL,
	ac_rate numeric(5, 2) NOT NULL,
	ajrn_def_id _int4 NULL);	"
);
if ( ! $ret ){
    die ("cannot create public.test_array_int");
}
$ret=pg_query($cnx,
    "insert into public.test_array_int(ac_label,ac_rate,ajrn_def_id) values ('ligne 1',1.2,'{1,2}')");

$ret=pg_query_params($cnx,
    "insert into public.test_array_int(ac_label,ac_rate,ajrn_def_id) values ($1,$2,$3)",
array('ligne 1',1.2,'{1,2}'));


$ret=pg_query($cnx,"drop table if exists public.test_array_int");