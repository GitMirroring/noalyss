#!/bin/bash

export PGCLUSTER=10/main
DOSSIER_TEST=rel70dossier25
FILE_TEST=dossier25.sql

dropdb $DOSSIER_TEST
if  [ $? -ne 0 ] ; then
		echo cannot drop $DOSSIER_TEST
		exit 1
fi

createdb $DOSSIER_TEST
psql -X  $DOSSIER_TEST  < db/$FILE_TEST

