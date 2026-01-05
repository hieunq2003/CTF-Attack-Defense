FROM php:8.2-apache

###########################################
# PHP EXTENSIONS
###########################################
RUN docker-php-ext-install pdo pdo_mysql mysqli

###########################################
# SYSTEM PACKAGES + MARIADB LOCAL
###########################################
RUN apt-get update && apt-get install -y \
    mariadb-server mariadb-client \
    openssh-server sudo \
    nmap sqlmap hydra gobuster \
    netcat-openbsd net-tools \
    tcpdump tshark \
    iputils-ping iptables \
    curl wget git unzip tmux \
    nano vim htop lsof \
    tree \
    && rm -rf /var/lib/apt/lists/*

###########################################
# NIKTO
###########################################
RUN git clone https://github.com/sullo/nikto /opt/nikto && \
    ln -s /opt/nikto/program/nikto.pl /usr/local/bin/nikto

###########################################
# SSH ROOT (BTC dùng, không public cho team)
###########################################
RUN mkdir -p /var/run/sshd \
 && sed -i 's/#\?PermitRootLogin.*/PermitRootLogin yes/' /etc/ssh/sshd_config \
 && echo "root:root" | chpasswd

###########################################
# TEAM USER (sudoers sẽ tạo trong entrypoint)
###########################################
RUN useradd -m -s /bin/bash team \
    && echo "team:changeme" | chpasswd \
    && mkdir -p /home/team \
    && chown -R team:team /home/team

###########################################
# FIX MYSQL/MARIADB PERMISSIONS
###########################################
RUN mkdir -p /var/run/mysqld \
 && chown -R mysql:mysql /var/lib/mysql /var/run/mysqld

###########################################
# COPY CHALLENGE SIS - 1
###########################################
COPY docker/challenge/service1/src/ /var/www/service1/

###########################################
# COPY CHALLENGE SMS - 2
###########################################
COPY docker/challenge/service2/src/ /var/www/service2/

###########################################
# COPY CHALLENGE block-puzzle - 3
###########################################
COPY docker/challenge/service3/src/ /var/www/service3/

###########################################
# QUYỀN WEBROOT CHO TEAM + APACHE
###########################################
RUN chown -R team:www-data /var/www/service1 /var/www/service2 /var/www/service3 \
    && chmod -R 750 /var/www/service1 /var/www/service2 /var/www/service3

###########################################
# COPY DATABASE SQL SIS
###########################################
COPY docker/challenge/service1/src/database/sis_db.sql /opt/sis_db.sql

###########################################
# COPY DATABASE SQL SMS
###########################################
COPY docker/challenge/service2/src/database/sms_db2.sql /opt/sms_db2.sql

###########################################
# COPY DATABASE SQL block-puzzle
###########################################
COPY docker/challenge/service3/src/database/block_puzzle.sql /opt/block_puzzle.sql

###########################################
# APACHE VHOST
###########################################
COPY docker/challenge/service1.conf /etc/apache2/sites-available/service1.conf
COPY docker/challenge/service2.conf /etc/apache2/sites-available/service2.conf
COPY docker/challenge/service3.conf /etc/apache2/sites-available/service3.conf

# Cho Apache (www-data) có quyền ghi backup + .flags
RUN mkdir -p /var/www/service2/backup/.flags \
    && chown -R www-data:www-data /var/www/service2/backup \
    && chmod 750 /var/www/service2/backup /var/www/service2/backup/.flags

RUN chown -R www-data:www-data /var/www/service3/database
# flags: KHÔNG cho team đụng
# Thư mục flags (ngoài docroot) + uploads (trong webroot) cho service3
RUN mkdir -p /var/www/service3/flags \
    && mkdir -p /var/www/service3/uploads \
    # Gán owner chung cho toàn bộ service3 là www-data
    && chown -R www-data:www-data /var/www/service3 \
    # flags: chỉ Apache (www-data) đọc/ghi → team không đụng được
    && chmod 750 /var/www/service3/flags \
    # uploads: Apache ghi, mọi người (kể cả teamX_Y) đọc được
    && chmod 755 /var/www/service3/uploads

# Thêm dòng này để Apache mở port 81 và 82
RUN echo "Listen 81\nListen 82" >> /etc/apache2/ports.conf \
 && a2dissite 000-default.conf \
 && a2ensite service1 \
 && a2ensite service2 \
 && a2ensite service3

###########################################
# ENTRYPOINT
###########################################
COPY docker/challenge/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 81 82 22

CMD ["/usr/local/bin/docker-entrypoint.sh"]
