#!/bin/bash

export PGCLUSTER=10/main
DOSSIER_TEST=rel70dossier25
FILE_TEST=dossiertest201107-1806.sql

dropdb $DOSSIER_TEST
createdb $DOSSIER_TEST
psql -X  $DOSSIER_TEST  < db/$FILE_TEST

