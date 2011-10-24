#/bin/bash

BASEDIR=`readlink -f "$( dirname $0 )/.."`
AGAVI_SOURCE_DIRECTORY="${BASEDIR}/libs/agavi"
LOCAL_CONFIG_SH="${BASEDIR}/etc/local/local.config.sh"

if [ -f $LOCAL_CONFIG_SH ] ; then
  . $LOCAL_CONFIG_SH
else
  echo "Required config file does not exists: ${LOCAL_CONFIG_SH}"
  echo "Have you run bin/configure-env.php allready?"
  exit 0
fi

JSDOC_DIR="${BASEDIR}/dev/jsdoc-toolkit"
TPL_DIR="${JSDOC_DIR}/templates/codeview"
JSSRC_DIR="${BASEDIR}/pub/js/midas"
JSDOC_CMD="java -jar ${JSDOC_DIR}/jsrun.jar ${JSDOC_DIR}/app/run.js -a -t=${TPL_DIR} ${@}"
echo "Executing: ${JSDOC_CMD}"
$JSDOC_CMD
