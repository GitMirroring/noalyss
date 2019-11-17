#!/bin/bash

DOSSIER_TEST=rel70dossier25

dropdb $DOSSIER_TEST
createdb $DOSSIER_TEST
pg_restore -Fc --no-owner --no-privilege --verbose  -d $DOSSIER_TEST  db/dossiertest191117-0909.bin

