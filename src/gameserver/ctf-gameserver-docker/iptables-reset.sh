#!/bin/bash
echo "[+] Clearing only DOCKER-USER rules..."
iptables -t filter -F DOCKER-USER
echo "[+] All custom rules removed. Docker default preserved."
