<?php
/*****************************
 *
 * RouterOS PHP API Class 1.6 (Hybrid Fix)
 * Optimized for RouterOS v6 & v7
 * *****************************/
class RouterosAPI {
    var $debug = false;
    var $connected = false;
    var $port = 8728;
    var $timeout = 3;
    var $attempts = 3; 
    var $delay = 1;
    var $socket;
    var $error_no;
    var $error_str;

    function connect($host, $login, $password, $port = 8728) {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $this->socket = @fsockopen($host, $port, $this->error_no, $this->error_str, $this->timeout);
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                
                // --- Modern Login Protocol (v7 Compatibility) ---
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                
                $RESPONSE = $this->read(false);
                
                if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                    $this->connected = true;
                    break;
                }

                // --- Fallback to Legacy Login (v6 and older) ---
                $this->write('/login');
                $RESPONSE = $this->read(false);
                if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done' && isset($RESPONSE[1])) {
                    $hash = md5(pack('H*', '00') . $password . pack('H*', substr($RESPONSE[1], 5)));
                    $this->write('/login', false);
                    $this->write('=name=' . $login, false);
                    $this->write('=response=00' . $hash);
                    $RESPONSE = $this->read(false);
                    if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                        $this->connected = true;
                        break;
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }
        return $this->connected;
    }

    function disconnect() {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->connected = false;
    }

    function comm($com, $arr = array()) {
        $count = count($arr);
        $this->write($com, $count == 0);
        $i = 1;
        foreach ($arr as $k => $v) {
            // Ensure properties start with '='
            $prefix = (strpos($k, '=') === 0) ? "" : "=";
            $this->write($prefix . $k . '=' . $v, $i == $count);
            $i++;
        }
        return $this->read();
    }

    function write($command, $tag = true) {
        $byte_str = '';
        $length = strlen($command);
        if ($length < 128) $byte_str = chr($length);
        else if ($length < 16384) $byte_str = chr(($length >> 8) | 128) . chr($length & 255);
        else if ($length < 2097152) $byte_str = chr(($length >> 16) | 192) . chr(($length >> 8) & 255) . chr($length & 255);
        
        fwrite($this->socket, $byte_str . $command);
        if ($tag) fwrite($this->socket, chr(0));
    }

    function read($parse = true) {
        $RESPONSE = array();
        while (true) {
            $read_byte = fread($this->socket, 1);
            if ($read_byte === false || strlen($read_byte) === 0) break;
            $byte = ord($read_byte);
            if ($byte == 0) break;
            
            if ($byte < 128) $len = $byte;
            else if ($byte < 192) $len = (($byte & 63) << 8) + ord(fread($this->socket, 1));
            else $len = (($byte & 31) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            
            $word = fread($this->socket, $len);
            
            if ($parse) {
                if (preg_match("/^=(.*)=(.*)/", $word, $matches)) {
                    $last_index = count($RESPONSE) - 1;
                    // If the last item isn't an array, initialize it
                    if ($last_index < 0 || !is_array($RESPONSE[$last_index])) {
                        $RESPONSE[] = array();
                        $last_index = count($RESPONSE) - 1;
                    }
                    $RESPONSE[$last_index][$matches[1]] = $matches[2];
                } else {
                    $RESPONSE[] = $word;
                }
            } else {
                $RESPONSE[] = $word;
            }
        }
        return $RESPONSE;
    }
}
?>