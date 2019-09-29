#!/bin/bash

DOSSIER_TEST=dossier48

pg_restore -Fc --no-owner --no-privilege --verbose  -d $DOSSIER_TEST  db/dossiertest.bin

