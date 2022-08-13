begin;
create table parm_appearance (a_code text primary key , a_value text not null);

insert into parm_appearance values ('H2' , '#9fbcd6')
                                 ,('MENU1','#000074')
                                 ,('BODY','#ffffff')
                                 ,('MENU2','#3d3d87')
                                 ,('MENU1-SELECTED','#3d3d87')
                                 ,('FONT-MENU1','#ffffff')
                                 ,('FONT-MENU2','#ffffff')
                                 ,('FONT-TABLE','#222bd0')
                                 ,('FONT-DEFAULT','#000074')
                                 ,('FONT-TABLE-HEADER','#0C106D')
                                 ,('FOLDER','#ffffff')
                                 ,('FONT-FOLDER','#000074')
                                 ,('TR-ODD','#DCE7F5')
                                 ,('TR-EVEN','#ffffff')
                                 ,('INNER-BOX','#DCE1EF')
                            ;
insert into version (val,v_description) values (175,'Folder Appearance');
commit;
