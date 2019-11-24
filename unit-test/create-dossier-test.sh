#!/bin/bash

DOSSIER_TEST=rel70dossier25
FILE_TEST=dossiertest191124-2109.bin

dropdb $DOSSIER_TEST
createdb $DOSSIER_TEST
pg_restore -Fc --no-owner --no-privilege --verbose  -d $DOSSIER_TEST  db/$FILE_TEST

