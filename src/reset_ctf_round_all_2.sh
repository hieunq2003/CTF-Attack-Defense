#!/bin/bash
set -euo pipefail

echo "=== [1] DUNG CTF CONTROLLER + CAC CHECKER ==="

systemctl stop ctf-checkermaster@sis.service || true
systemctl stop ctf-checkermaster@sms.service || true
systemctl stop ctf-checkermaster@bp.service  || true
systemctl stop ctf-controller.service        || true
systemctl stop ctf-submission.service        || true
systemctl stop ctf-vpnstatus.service         || true

echo "=== [2] RESET DB SCORING TRONG POSTGRES (ctf DB) ==="

COMPOSE_DIR="/opt/ctf-gameserver-docker"
POSTGRES_SERVICE="db"
POSTGRES_DB="ctf"
POSTGRES_USER="ctf"

cd "$COMPOSE_DIR"

docker compose exec -T "$POSTGRES_SERVICE" psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" <<'EOF'
DELETE FROM scoring_statuscheck;
DELETE FROM scoring_capture;
DELETE FROM scoring_flag;
DELETE FROM scoring_scoreboard;
UPDATE scoring_gamecontrol SET current_tick = -1;
EOF

echo "=== [3] RESET flag_storage SIS + FILE FLAG SMS + FILE FLAG BP TREN CAC TEAM ==="

TEAM_DB_NAME="sis_db"
TEAM_DB_USER="ctf"
TEAM_DB_PASS="ctfpass"

# IP node chạy 3 team container
TEAM_HOST_IP="160.250.132.171"

# Port SSH từng team
TEAM_PORTS=(10001 10002 10003)

# Thư mục chứa flag SMS trong mỗi team (service2)
SMS_FLAG_DIR="/var/www/service2/backup/.flags"

# Thư mục chứa flag BP (service3 – RCE upload)
SERVICE3_FLAGS_DIR="/var/www/service3/flags"

# (Legacy) File JSON & DB cho service3 (cũ)
SERVICE3_HINTS_JSON="/var/www/service3/database/hints.json"
SERVICE3_DB_NAME="block_puzzle"

for port in "${TEAM_PORTS[@]}"; do
  echo ">>> Dang reset tren ${TEAM_HOST_IP}:${port} ..."

  sshpass -p 'root' ssh -p "${port}" \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    root@"${TEAM_HOST_IP}" "
      echo '  - Reset bang flag_storage SIS...'
      mysql -u${TEAM_DB_USER} -p'${TEAM_DB_PASS}' ${TEAM_DB_NAME} -e \"
        CREATE TABLE IF NOT EXISTS flag_storage (
          id   INT(11) NOT NULL AUTO_INCREMENT,
          tick INT(11) NOT NULL,
          flag VARCHAR(128) NOT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY tick (tick)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        TRUNCATE TABLE flag_storage;
      \"

      echo '  - Xoa file flag SMS (.flags)...'
      if [ -d \"${SMS_FLAG_DIR}\" ]; then
        rm -f \"${SMS_FLAG_DIR}\"/tick_*.flag || true
      fi

      echo '  - Xoa file flag BP (/var/www/service3/flags)...'
      if [ -d \"${SERVICE3_FLAGS_DIR}\" ]; then
        rm -f \"${SERVICE3_FLAGS_DIR}\"/flag_*.txt || true
      fi

      echo '  - (Legacy) Reset puzzle_hints + xoa hints.json neu ton tai...'
      mysql -u${TEAM_DB_USER} -p'${TEAM_DB_PASS}' ${SERVICE3_DB_NAME} -e \"
        CREATE TABLE IF NOT EXISTS puzzle_hints (
          id        INT(11) NOT NULL AUTO_INCREMENT,
          stage     INT(11) NOT NULL,
          hint_type VARCHAR(64) NOT NULL,
          content   TEXT NOT NULL,
          updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        TRUNCATE TABLE puzzle_hints;
      \"

      if [ -f \"${SERVICE3_HINTS_JSON}\" ]; then
        rm -f \"${SERVICE3_HINTS_JSON}\" || true
      fi
    "

  echo "    -> Done team (port ${port})"
done

echo "=== [4] START LAI CONTROLLER + CAC CHECKER ==="

systemctl daemon-reload

systemctl start ctf-controller.service
systemctl start ctf-checkermaster@sis.service
systemctl start ctf-checkermaster@sms.service
systemctl start ctf-checkermaster@bp.service
systemctl start ctf-submission.service
# Neu can: systemctl start ctf-vpnstatus.service
#clear fastlog
truncate -s 0 /var/log/suricata/fast.log


echo "=== [5] KIEM TRA NHANH ==="
echo "Go:"
echo "  journalctl -u ctf-controller.service -f"
echo "  journalctl -u ctf-checkermaster@sis.service -f"
echo "  journalctl -u ctf-checkermaster@sms.service -f"
echo "  journalctl -u ctf-checkermaster@bp.service -f"
echo "De xem tick chay lai tu 0 va ca 3 checker OK."
echo ">>> RESET HOAN TAT."
