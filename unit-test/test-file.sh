#!/bin/bash
help(){ 
	echo "$0 -f filename [-i function ]"
	echo "     -f filename , file to test"
	echo "     -d folder to test "
	echo "     -i function , optional filter to a function"
	echo "     -c in the folder coverage , show the coverage"
	echo "     -x load php.ini file"
}

cd `dirname $0`

CUR_DIR=`pwd`
PHPUNIT=$CUR_DIR/phpunit
FILETOTEST=""
FUNCTION=""
COVERAGE=""
FOLDERTEST=""
#PHPINI="/usr/bin/php"
PHPINI="/usr/bin/php "
while getopts "f:i:cd:x" opt; do
	case $opt in
		d)
			FOLDERTEST=$OPTARG
			;;
		f)
			FILETOTEST=$OPTARG
			;;
		i)
			FUNCTION=$OPTARG
			;;
		c)	COVERAGE="--whitelist=../include --coverage-html=coverage"
			;;
		x)
			PHPINI=$PHPINI" -c php.ini "
			;;
		*)
			help
			exit 1
	esac
done

if [ -z "$FILETOTEST" -a -z "$FOLDERTEST" ] ;then
	help
	echo "please give a file or a folder to test "
	exit 2
fi	

if [ ! -z "$FOLDERTEST" -a ! -d "$FOLDERTEST" ] ; then
	echo "Folder doesn't exist"
	exit 3
fi


if [ ! -z "$FOLDERTEST" -a  -d "$FOLDERTEST" ] ; then

	$PHPINI $PHPUNIT --bootstrap bootstrap.php $COVERAGE --testdox --color $FOLDERTEST
	exit $?
fi

if [ ! -f "$FILETOTEST" ] ; then
	echo "File $FILETOTEST not found"
	exit 1
fi

if [ ! -z "$FUNCTION" ] ; then
	echo "testing $FILETOTEST $FUNCTION"
	$PHPINI $PHPUNIT --bootstrap $CUR_DIR/bootstrap.php --verbose --color --filter $FUNCTION $FILETOTEST
else

	# $PHPUNIT --bootstrap bootstrap.php --whitelist $FILETOTEST --coverage-text=${FILETOTEST%.php}.txt  --color $FILETOTEST 
	# $PHPUNIT --bootstrap $CUR_DIR/bootstrap.php --whitelist=$CUR_DIR/../include/class --coverage-html=coverage --color $FILETOTEST 
	$PHPINI $PHPUNIT --bootstrap bootstrap.php $COVERAGE $FILETOTEST --testdox --color $FILETOTEST 
	#$PHPUNIT --bootstrap bootstrap.php --whitelist=../include --coverage-html=coverage $FILETOTEST --testdox-html ${FILETOTEST%.php}-testdox.html --color $FILETOTEST 
fi

