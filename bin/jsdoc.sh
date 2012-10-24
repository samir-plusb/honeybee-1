#/bin/bash

BASEDIR=`readlink -f "$( dirname $0 )/.."`
LOCAL_CONFIG_SH="${BASEDIR}/etc/local/local.config.sh"

if [ -f $LOCAL_CONFIG_SH ] ; then
  . $LOCAL_CONFIG_SH
else
  echo "Required config file does not exists: ${LOCAL_CONFIG_SH}"
  echo "Have you run bin/configure-env.php allready?"
  exit 0
fi

OUT_DIR="${BASEDIR}/etc/integration/build/api/clientside"
JSSRC_DIR="${BASEDIR}/pub/js/midas"
ndoc "${JSSRC_DIR}" -o "${OUT_DIR}"
