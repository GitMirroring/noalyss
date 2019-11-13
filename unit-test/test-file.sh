#!/bin/bash
help(){ 
	echo "$0 -f filename [-i function ]"
	echo "     -f filename , file to test"
	echo "     -i function , optional filter to a function"
}

cd `dirname $0`

CUR_DIR=`pwd`
PHPUNIT=$CUR_DIR/phpunit
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
	$PHPUNIT --bootstrap $CUR_DIR/bootstrap.php --verbose --color --filter $FUNCTION $FILETOTEST
else

	# $PHPUNIT --bootstrap bootstrap.php --whitelist $FILETOTEST --coverage-text=${FILETOTEST%.php}.txt  --color $FILETOTEST 
	$PHPUNIT --bootstrap $CUR_DIR/bootstrap.php --whitelist=$CUR_DIR/../include/class --coverage-html=coverage --color $FILETOTEST 
	#$PHPUNIT --bootstrap bootstrap.php $FILETOTEST --testdox-html ${FILETOTEST%.php}-testdox.html --color $FILETOTEST 
fi

