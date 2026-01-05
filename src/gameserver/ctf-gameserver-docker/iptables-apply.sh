#!/bin/bash

# =============================
#  Detect interface dynamically
# =============================

WAN_IF="eth0"

DMZ_PUBLIC=$(ip -o -4 addr show | grep "172\.18\.0\.1/16" | awk '{print $2}')
DMZ_BACKEND=$(ip -o -4 addr show | grep "172\.19\.0\.1/16" | awk '{print $2}')
INTERNAL_IF=$(ip -o -4 addr show | grep "10\.50\.0\.1/16" | awk '{print $2}')

# Host IP (for submission/checker systemd services)
HOST_IP=$(ip -4 addr show $WAN_IF | grep -oP '(?<=inet\s)\d+(\.\d+){3}')

echo "[+] Interfaces detected:"
echo "WAN_IF      = $WAN_IF"
echo "DMZ_PUBLIC  = $DMZ_PUBLIC"
echo "DMZ_BACKEND = $DMZ_BACKEND"
echo "INTERNAL_IF = $INTERNAL_IF"
echo "HOST_IP     = $HOST_IP"

# =============================
#   Reset chain DOCKER-USER
# =============================

iptables -t filter -F DOCKER-USER

# =============================
#   NAT for internal → WAN
# =============================

iptables -t nat -A POSTROUTING -s 10.50.0.0/16 -o $WAN_IF -j MASQUERADE

# NAT hairpin for internal → host services
iptables -t nat -A POSTROUTING -s 10.50.0.0/16 -d $HOST_IP -j MASQUERADE

# =============================
#   Always allow established
# =============================

iptables -A DOCKER-USER -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT

# =============================
#   WAN rules
# =============================

# Portal
iptables -A DOCKER-USER -i $WAN_IF -p tcp --dport 80 -j ACCEPT
iptables -A DOCKER-USER -i $WAN_IF -p tcp --dport 443 -j ACCEPT

# Team SSH
iptables -A DOCKER-USER -i $WAN_IF -p tcp --match multiport --dports 10001:10010 -j ACCEPT

# Drop all other inbound WAN
iptables -A DOCKER-USER -i $WAN_IF -j DROP

# =============================
#   INTERNAL ZONE RULES
# =============================

# Internal → internal (team attacking each other)
iptables -A DOCKER-USER -i $INTERNAL_IF -o $INTERNAL_IF -j ACCEPT

# Internal → Internet
iptables -A DOCKER-USER -i $INTERNAL_IF -o $WAN_IF -j DROP

# =============================
#   Submission & Checker
#   (Services running systemd on host)
# =============================

# Internal → HOST:6666 (submission)
iptables -A DOCKER-USER -i $INTERNAL_IF -d $HOST_IP -p tcp --dport 6666 -j ACCEPT

# HOST → Internal:80 (checker)
iptables -A DOCKER-USER -s $HOST_IP -o $INTERNAL_IF -p tcp --dport 80 -j ACCEPT

# =============================
#   DMZ Docker-Only Rules
# =============================

# Internal → DMZ backend:6666 (if submission is proxied here)
iptables -A DOCKER-USER -i $INTERNAL_IF -o $DMZ_BACKEND -p tcp --dport 6666 -j ACCEPT

# DMZ backend → Internal (checker 80)
iptables -A DOCKER-USER -i $DMZ_BACKEND -o $INTERNAL_IF -p tcp --dport 80 -j ACCEPT

# Block all other internal → DMZ
iptables -A DOCKER-USER -i $INTERNAL_IF -o $DMZ_PUBLIC -j DROP
iptables -A DOCKER-USER -i $INTERNAL_IF -o $DMZ_BACKEND -j DROP

echo "[+] Firewall rules applied successfully."
