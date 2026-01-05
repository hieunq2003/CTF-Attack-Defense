#!/bin/bash

###########################################
# TEAM USERS DYNAMIC (team1_1..4, team2_1..4, ...)
###########################################

# TEAM_USER bây giờ đóng vai trò PREFIX (vd: team2)
TEAM_PREFIX="${TEAM_USER:-team}"
TEAM_PASSWORD="${TEAM_PASSWORD:-}"

echo "[*] Using TEAM_PREFIX='${TEAM_PREFIX}'"

# Nếu trong image có user 'team' và chưa có ${TEAM_PREFIX}_1 thì có thể rename cho gọn
if id team >/dev/null 2>&1 && ! id "${TEAM_PREFIX}_1" >/dev/null 2>&1; then
    echo "[*] Renaming user 'team' -> '${TEAM_PREFIX}_1'"
    usermod -l "${TEAM_PREFIX}_1" team
    usermod -d "/home/${TEAM_PREFIX}_1" -m "${TEAM_PREFIX}_1"
fi

# Tạo 4 user TEAM_PREFIX_1..4, đặt password = TEAM_PASSWORD (vd: 123)
for i in 1 2 3 4; do
    USERNAME="${TEAM_PREFIX}_${i}"

    if id "${USERNAME}" >/dev/null 2>&1; then
        echo "[*] User '${USERNAME}' đã tồn tại, bỏ qua useradd."
    else
        echo "[*] Creating user '${USERNAME}'"
        useradd -m -s /bin/bash "${USERNAME}" || echo "[!] Không tạo được user ${USERNAME}"
    fi

    if [ -n "${TEAM_PASSWORD}" ]; then
        echo "[*] Setting password for '${USERNAME}' từ ENV TEAM_PASSWORD"
        echo "${USERNAME}:${TEAM_PASSWORD}" | chpasswd || \
            echo "[!] Không đặt được password cho user ${USERNAME}"
    else
        echo "[!] TEAM_PASSWORD không được set, user '${USERNAME}' giữ password mặc định (KHÔNG nên dùng khi mở cho team)."
    fi
done

###########################################
# SHARE quyền /var/www cho cả 4 user cùng team
###########################################

if [ -d /var/www ]; then
    SHARED_GROUP="ctfwww"

    # Tạo group chung nếu chưa có
    if ! getent group "$SHARED_GROUP" >/dev/null 2>&1; then
        groupadd "$SHARED_GROUP"
    fi

    # Thêm 4 user teamX_1..4 vào group chung
    for i in 1 2 3 4; do
        USERNAME="${TEAM_PREFIX}_${i}"
        if id "$USERNAME" >/dev/null 2>&1; then
            usermod -a -G "$SHARED_GROUP" "$USERNAME" 2>/dev/null || true
        fi
    done

    # Thêm user chạy Apache/PHP vào group chung để nó ghi được vào /var/www
    if id www-data >/dev/null 2>&1; then
        usermod -a -G "$SHARED_GROUP" www-data 2>/dev/null || true
    fi

    echo "[*] Chia sẻ /var/www cho group $SHARED_GROUP (teamX_1..4 + www-data)"

    # Owner: user 1, group: ctfwww
    chown -R "${TEAM_PREFIX}_1:${SHARED_GROUP}" /var/www 2>/dev/null || true

    # Thư mục: setgid + rwx cho owner/group
    find /var/www -type d -exec chmod 2775 {} \; 2>/dev/null || true
    # File: rw-rw-r--
    find /var/www -type f -exec chmod 664 {} \; 2>/dev/null || true

    # Đảm bảo thư mục database cho SMS/BP tồn tại và writable
    for svc in service2 service3; do
        if [ -d "/var/www/${svc}" ]; then
            mkdir -p "/var/www/${svc}/database"
            chown -R "${TEAM_PREFIX}_1:${SHARED_GROUP}" "/var/www/${svc}/database" 2>/dev/null || true
            chmod 2775 "/var/www/${svc}/database" 2>/dev/null || true
        fi
    done
fi


###########################################
# TMUX (cho phép 1 SSH mở nhiều tab bên trong)
###########################################

if ! command -v tmux >/dev/null 2>&1; then
    echo "[*] Installing tmux..."
    apt-get update -y >/dev/null 2>&1 || true
    apt-get install -y tmux >/dev/null 2>&1 || echo "[!] Cài tmux thất bại, kiểm tra lại nếu cần."
fi

###########################################
# SUDOERS cho team user
###########################################

cat >/etc/sudoers.d/ctfteam <<EOF
Defaults:${TEAM_PREFIX}_1 !requiretty
Defaults:${TEAM_PREFIX}_2 !requiretty
Defaults:${TEAM_PREFIX}_3 !requiretty
Defaults:${TEAM_PREFIX}_4 !requiretty

${TEAM_PREFIX}_1 ALL=(root) NOPASSWD: \
    /usr/sbin/service, \
    /usr/sbin/apache2ctl, \
    /usr/bin/mysql, \
    /usr/sbin/iptables, \
    /usr/bin/tcpdump, \
    /usr/bin/nmap, \
    /usr/bin/sqlmap, \
    /usr/bin/hydra, \
    /usr/bin/gobuster, \
    /usr/local/bin/nikto, \
    /usr/bin/tshark, \
    /bin/nc, \
    /usr/bin/nc, \
    /usr/bin/nano

${TEAM_PREFIX}_2 ALL=(root) NOPASSWD: \
    /usr/sbin/service, \
    /usr/sbin/apache2ctl, \
    /usr/bin/mysql, \
    /usr/sbin/iptables, \
    /usr/bin/tcpdump, \
    /usr/bin/nmap, \
    /usr/bin/sqlmap, \
    /usr/bin/hydra, \
    /usr/bin/gobuster, \
    /usr/local/bin/nikto, \
    /usr/bin/tshark, \
    /bin/nc, \
    /usr/bin/nc, \
    /usr/bin/nano

${TEAM_PREFIX}_3 ALL=(root) NOPASSWD: \
    /usr/sbin/service, \
    /usr/sbin/apache2ctl, \
    /usr/bin/mysql, \
    /usr/sbin/iptables, \
    /usr/bin/tcpdump, \
    /usr/bin/nmap, \
    /usr/bin/sqlmap, \
    /usr/bin/hydra, \
    /usr/bin/gobuster, \
    /usr/local/bin/nikto, \
    /usr/bin/tshark, \
    /bin/nc, \
    /usr/bin/nc, \
    /usr/bin/nano

${TEAM_PREFIX}_4 ALL=(root) NOPASSWD: \
    /usr/sbin/service, \
    /usr/sbin/apache2ctl, \
    /usr/bin/mysql, \
    /usr/sbin/iptables, \
    /usr/bin/tcpdump, \
    /usr/bin/nmap, \
    /usr/bin/sqlmap, \
    /usr/bin/hydra, \
    /usr/bin/gobuster, \
    /usr/local/bin/nikto, \
    /usr/bin/tshark, \
    /bin/nc, \
    /usr/bin/nc, \
    /usr/bin/nano
EOF
chmod 440 /etc/sudoers.d/ctfteam || echo "[!] Không set chmod cho /etc/sudoers.d/ctfteam"

###########################################
# Token Lock Script + reset-session
# (mỗi account chỉ 1 session, dùng reset-session để giành quyền)
###########################################

# /etc/profile.d/tokenlock.sh: chạy cho mọi user khi SSH
cat >/etc/profile.d/tokenlock.sh <<'EOF'
#!/bin/bash

TOKEN_FILE="$HOME/.session_token"
RESET_FILE="$HOME/.session_reset"

# Chỉ chạy với SSH (tránh đụng shell local trong container)
[ -z "$SSH_CONNECTION" ] && return 0

# Nếu đang trong tmux (pane mới bên trong cùng 1 SSH) → bỏ qua lock
if [ -n "$TMUX" ]; then
    return 0
fi

# Nếu có flag reset -> clear token + flag (mở khóa lại)
if [ -f "$RESET_FILE" ]; then
    rm -f "$TOKEN_FILE" "$RESET_FILE"
fi

# Nếu đã có token, kiểm tra xem session chủ còn sống không
if [ -f "$TOKEN_FILE" ]; then
    OWNER_PID=$(cat "$TOKEN_FILE" 2>/dev/null || echo "")

    if [ -n "$OWNER_PID" ] && ps -p "$OWNER_PID" -o comm= 2>/dev/null | grep -q "sshd"; then
        # Chủ vẫn đang login → chặn session mới
        echo ""
        echo "❌ This account is already in use by another session."
        echo "👉 Ask teammate to run: reset-session"
        echo ""
        exit 1
    else
        # Token cũ đã stale (sshd cũ chết) → xóa, cho login mới lên làm chủ
        rm -f "$TOKEN_FILE"
    fi
fi

# Tới đây: KHÔNG có chủ hoặc chủ stale → gán phiên hiện tại làm owner
# Trong login shell, PPID chính là sshd cha của session này
echo "$PPID" > "$TOKEN_FILE"
chmod 600 "$TOKEN_FILE"
EOF
chmod 644 /etc/profile.d/tokenlock.sh || echo "[!] Không set chmod cho /etc/profile.d/tokenlock.sh"

# /usr/local/bin/reset-session: giành quyền sử dụng account
cat >/usr/local/bin/reset-session <<'EOF'
#!/bin/bash

TOKEN_FILE="$HOME/.session_token"
RESET_FILE="$HOME/.session_reset"

# Phải chạy từ session SSH (có SSH_CONNECTION) cho đúng ngữ cảnh
if [ -z "$SSH_CONNECTION" ]; then
    echo "reset-session must be run from an SSH session."
    exit 1
fi

# Đánh dấu reset để profile script lần login sau dọn token
touch "$RESET_FILE"

echo "Session reset. All SSH sessions for this user will now be terminated."

# KILL tất cả sshd của user này (kể cả session hiện tại)
for pid in $(pgrep -u "$USER" sshd); do
    kill -9 "$pid" 2>/dev/null
done
EOF
chmod +x /usr/local/bin/reset-session || echo "[!] Không set chmod cho /usr/local/bin/reset-session"

###########################################
# MariaDB CONFIG + INIT (dựa trên bản gốc)
###########################################

# Cho MariaDB listen trên 0.0.0.0 để container khác truy cập được
sed -i 's/^bind-address\s*=.*/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf 2>/dev/null || true

# Đảm bảo quyền cho thư mục MariaDB (do /var/lib/mysql được mount volume runtime)
mkdir -p /var/run/mysqld
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld 2>/dev/null || true

# Start MariaDB service
echo "[*] Starting MariaDB..."
service mariadb start
sleep 5

# Fix MariaDB root login so PHP can connect (idempotent)
# 1) Thử login root/root trước
if mysql -u root -proot -e "SELECT 1" >/dev/null 2>&1; then
    echo "[*] MariaDB: root đã có password 'root', bỏ qua ALTER USER."
else
    # 2) Thử login root không pass (trường hợp mới cài)
    if mysql -u root -e "SELECT 1" >/dev/null 2>&1; then
        echo "[*] MariaDB: đang đặt password cho root = 'root'"
        mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('root');
FLUSH PRIVILEGES;
EOF
    else
        echo "[!] MariaDB: KHÔNG login được với root (không pass, cũng không với pass 'root'). Kiểm tra lại nếu cần."
    fi
fi

# Initialize database only if first time
if [ ! -d "/var/lib/mysql/sis_db" ]; then
    echo "[*] Initializing SIS database..."
    mysql -u root -proot -e "CREATE DATABASE sis_db;" 2>/dev/null || echo "[!] CREATE DATABASE sis_db failed (có thể đã tồn tại)."
    mysql -u root -proot sis_db < /opt/sis_db.sql 2>/dev/null || echo "[!] Import sis_db.sql failed."
fi

if [ ! -d "/var/lib/mysql/sms_db2" ]; then
    echo "[*] Initializing SMS database..."
    mysql -u root -proot -e "CREATE DATABASE sms_db2;" 2>/dev/null || echo "[!] CREATE DATABASE sms_db2 failed."
    mysql -u root -proot sms_db2 < /opt/sms_db2.sql 2>/dev/null || echo "[!] Import sms_db2.sql failed."
fi

if [ ! -d "/var/lib/mysql/block_puzzle" ]; then
    echo "[*] Initializing block-puzzle database..."
    mysql -u root -proot -e "CREATE DATABASE block_puzzle;" 2>/dev/null || echo "[!] CREATE DATABASE block_puzzle failed."
    mysql -u root -proot block_puzzle < /opt/block_puzzle.sql 2>/dev/null || echo "[!] Import block_puzzle.sql failed."
fi

# Tạo user riêng cho checker để connect từ network ctf_internal
mysql -u root -proot <<EOF 2>/dev/null || echo "[!] CREATE USER/GRANT cho 'ctf' thất bại (có thể đã tồn tại)."
CREATE USER IF NOT EXISTS 'ctf'@'%' IDENTIFIED BY 'ctfpass';
GRANT ALL PRIVILEGES ON sis_db.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON sms_db2.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON block_puzzle.* TO 'ctf'@'%';
FLUSH PRIVILEGES;
EOF

###########################################
# SSH + APACHE (y như bản entry gốc)
###########################################

echo "[*] Starting SSH..."
service ssh start

echo "[*] Starting Apache (apache2-foreground)..."
apache2-foreground
