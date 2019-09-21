#!/bin/bash

PHPUNIT=./phpunit
if [ ! -f "$1" ] ; then
	echo "File $1 not found"
	exit 1
fi

$PHPUNIT --bootstrap bootstrap.php --color $1
