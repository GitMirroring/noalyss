#!/bin/bash

DOSSIER_TEST=rel70dossier25
FILE_TEST=dossiertest200318-1402.sql

dropdb $DOSSIER_TEST
createdb $DOSSIER_TEST
psql -X  $DOSSIER_TEST  < db/$FILE_TEST

