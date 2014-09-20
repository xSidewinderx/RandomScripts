#!/bin/sh
IPTABLES=/sbin/iptables
case "$1" in
start)
        echo -n "Starting IP Firewall ... "
        # Clear any existing rules, zero counters
        $IPTABLES -t nat    -F
        $IPTABLES -t mangle -F
        $IPTABLES -t filter -F
        $IPTABLES -Z
        # Set default policies
        $IPTABLES -P INPUT   DROP
        $IPTABLES -P FORWARD DROP
        $IPTABLES -P OUTPUT  ACCEPT
        # Allow loopback
        $IPTABLES -A INPUT -i lo -j ACCEPT
        # Allow packets from established or related connections
        # This rule affects all protocols (tcp, udp, icmp)
        $IPTABLES -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
	# iptables -A INPUT -s 31.13.69.55 -p tcp --destination-port 25 -j DROP
	# iptables -A INPUT -s 184.26.142.16 -p tcp --destination-port 25 -j DROP
	# /sbin/iptables -A INPUT -p tcp --destination-port 80 -j DROP	
	# Allow incoming SSH
        $IPTABLES -A INPUT -p tcp --dport ssh -j ACCEPT
        echo "done"
        ;;
stop)
        echo -n "Stopping IP Firewall ... "
        # Clear any existing rules, zero counters
        $IPTABLES -t nat    -F
        $IPTABLES -t mangle -F
        $IPTABLES -t filter -F
        $IPTABLES -Z
        # Allow everything
        $IPTABLES -P INPUT   ACCEPT
        $IPTABLES -P FORWARD ACCEPT
        $IPTABLES -P OUTPUT  ACCEPT
        echo "done"
        ;;
restart)
        echo -n "Restarting IP Firewall ... "
        # Stop, then start
        $0 stop  > /dev/null
        sleep 1
        $0 start > /dev/null
        echo "done"
        ;;
lock|lockdown|panic|shutdown|deny|denyall)
        echo -n "Locking down IP Firewall (disallow all network traffic) ... "
        # Clear any existing rules, zero counters
        $IPTABLES -t nat    -F
        $IPTABLES -t mangle -F
        $IPTABLES -t filter -F
        $IPTABLES -Z
        # Shut everything down
        $IPTABLES -P INPUT   DROP
        $IPTABLES -P FORWARD DROP
        $IPTABLES -P OUTPUT  DROP
        # Allow loopback (LOCALHOST, 127.0.0.1)
        $IPTABLES -A INPUT -i lo -j ACCEPT
        # Allow SSH Access
        $IPTABLES -I INPUT -p tcp --dport 22 -j ACCEPT
        echo "done"
        ;;
*)
        echo "Usage: $0 {start|stop|restart|panic}"
        ;;
esac
