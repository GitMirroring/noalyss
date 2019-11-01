#!/bin/bash

DOSSIER_TEST=rel70dossier25

createdb $DOSSIER_TEST
pg_restore -Fc --no-owner --no-privilege --verbose  -d $DOSSIER_TEST  db/dossiertest191101-2109.bin

