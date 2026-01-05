#!/bin/bash
# Chạy checker SIS cho tất cả team (1..3) với tick nhất định
# Usage: ./run_sis_manual.sh <TICK>   (vd: ./run_sis_manual.sh 0)

TICK="$1"

if [ -z "$TICK" ]; then
  echo "Usage: $0 <tick>"
  exit 1
fi

for TEAM in 1 2 3; do
  echo "===== Running SIS checker for team ${TEAM}, tick ${TICK} ====="
  docker run --rm \
    --network ctf_internal \
    ctf-gameserver-docker-checker \
    python -m ctf_gameserver.checker.sis_checker 127.0.0.1 "${TEAM}" "${TICK}"
  echo
done
