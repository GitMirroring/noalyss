#!/bin/bash
help(){ 
	echo "$0 -f filename [-i function ]"
	echo "     -f filename , file to test"
	echo "     -i function , optional filter to a function"
}
PHPUNIT=./phpunit
FILETOTEST=""
FUNCTION=""
while getopts "f:i:" opt; do
	case $opt in
		f)
			FILETOTEST=$OPTARG
			;;
		i)
			FUNCTION=$OPTARG
			;;
		*)
			help
			exit 1
	esac
done

if [ -z "$FILETOTEST" ] ;then
	help
	echo "-f is mandatory"
	exit 2
fi	

if [ ! -f "$FILETOTEST" ] ; then
	echo "File $FILETOTEST not found"
	exit 1
fi

if [ ! -z "$FUNCTION" ] ; then
	echo "testing $FILETOTEST $FUNCTION"
	$PHPUNIT --bootstrap bootstrap.php --verbose --color --filter $FUNCTION $FILETOTEST
else

	$PHPUNIT --bootstrap bootstrap.php --color $FILETOTEST
fi

