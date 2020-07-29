#!/usr/bin/env bash

set -eo pipefail;

REPOSITORY="logstash"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"


TIMESTAMP=$(date +%s)

TAG=${TIMESTAMP} /usr/local/bin/docker-compose \
  -f "${SCRIPT_DIR}/deploy/docker-compose-${2}.yml" build \
    --compress \
    --parallel;

profile="eonx-dev-payment-gateway-admin"

aws ecr get-login-password \
  --profile="${profile}" \
  --region ap-southeast-2 | docker login \
  --username AWS \
  --password-stdin "${1}.dkr.ecr.ap-southeast-2.amazonaws.com/${REPOSITORY}";

TAG=${TIMESTAMP} /usr/local/bin/docker-compose  -f "${SCRIPT_DIR}/deploy/docker-compose-${2}.yml" push;
