#/bin/bash

if [ -z "$1" -o -z "$2" ] ; then
  echo "Enter a source and a target class name."
  exit 1
fi

BASEDIR="`dirname $0`/../app/"
BASEDIR=`readlink -f $BASEDIR`
echo "grep -rl '$1' $BASEDIR | xargs perl -pi -e 's/$1/$2/g'"

grep -rl "$1" "$BASEDIR" | xargs perl -pi -e "s/$1/$2/g"
