#!/bin/sh

PROJECT=`readlink -f $( dirname $0 )/../..`
LOCAL_CONFIG_SH=$PROJECT/etc/local/local.config.sh

if [ -f $LOCAL_CONFIG_SH ] ; then
  echo "[INFO] Found and sourcing the local.config.sh"
  . $LOCAL_CONFIG_SH
else
  echo "[ERROR] Could not source $LOCAL_CONFIG_SH!"
  echo "Make sure to run bin/configure-env before doing anything else after a fresh checkout"
fi

if (test -z "$PHP_COMMAND") ; then
  echo "[ERROR] Unable to find a valid php command!"
  echo "Have you run bin/configure-env.php allready?"
  exit 0
fi

if (test -z "$AGAVI_ENVIRONMENT") ; then
  echo "[ERROR] Unable to find your configured agavi environment!"
fi

AGAVI_ENVIRONMENT=testing."$AGAVI_ENVIRONMENT" $PHP_COMMAND -d html_errors=off -f "$PROJECT"/bin/cli.php import.fixtures

# Move this stuff to the fixtures action.
#curl -XDELETE localhost:9200/midas_fixtures
#curl -XDELETE localhost:9200/_river/workflow_river_fixtures/_meta
#./create_index
#./create_river
