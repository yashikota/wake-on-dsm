<?php

function sendMagicPacket($macAddress, $broadcastIp = '255.255.255.255', $port = 9) {
    $macBinary = pack('H*', str_replace(':', '', $macAddress));
    $packet = str_repeat(chr(0xFF), 6) . str_repeat($macBinary, 16);

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$sock) {
        return "Error: Could not create socket - " . socket_strerror(socket_last_error());
    }

    if (!socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1)) {
        socket_close($sock);
        return "Error: Could not set socket option - " . socket_strerror(socket_last_error());
    }

    $sent = socket_sendto($sock, $packet, strlen($packet), 0, $broadcastIp, $port);
    socket_close($sock);

    if (!$sent) {
        return "Error: Could not send packet - " . socket_strerror(socket_last_error());
    }

    return "Magic packet successfully sent to $macAddress on $broadcastIp:$port";
}

$macAddress = '';

if (isset($_POST['macSelect']) && $_POST['macSelect'] !== 'other') {
    $macAddress = $_POST['macSelect'];
}
elseif (isset($_POST['mac'])) {
    $macAddress = $_POST['mac'];
}

$message = '';

if ($macAddress) {
    $message = sendMagicPacket($macAddress);
} else {
    $message = "Error: No MAC address provided";
}

echo $message;
