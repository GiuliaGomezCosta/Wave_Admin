
<?php

// Gera um segredo Base32 para o autenticador
function totp_gerar_segredo(int $tamanho = 20): string {
    $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bytes = random_bytes($tamanho);
    $segredo = '';

    $buffer = 0;
    $bits = 0;

    foreach (str_split($bytes) as $byte) {
        $buffer = ($buffer << 8) | ord($byte);
        $bits += 8;

        while ($bits >= 5) {
            $bits -= 5;
            $segredo .= $alfabeto[($buffer >> $bits) & 31];
        }
    }

    if ($bits > 0) {
        $segredo .= $alfabeto[($buffer << (5 - $bits)) & 31];
    }

    return $segredo;
}

// Converte Base32 para bytes
function totp_base32_decodificar(string $segredo): string {
    $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $segredo = strtoupper(preg_replace('/[^A-Z2-7]/', '', $segredo));

    $buffer = 0;
    $bits = 0;
    $resultado = '';

    foreach (str_split($segredo) as $caractere) {
        $valor = strpos($alfabeto, $caractere);

        if ($valor === false) {
            continue;
        }

        $buffer = ($buffer << 5) | $valor;
        $bits += 5;

        if ($bits >= 8) {
            $bits -= 8;
            $resultado .= chr(($buffer >> $bits) & 255);
        }
    }

    return $resultado;
}

// Gera um código TOTP
function totp_gerar_codigo(string $segredo, ?int $tempo = null): string {
    $chave = totp_base32_decodificar($segredo);

    if ($chave === '') {
        return '';
    }

    $tempo = $tempo ?? time();
    $contador = (int) floor($tempo / 30);

    $contadorBinario = pack('N*', 0) . pack('N*', $contador);

    $hash = hash_hmac('sha1', $contadorBinario, $chave, true);

    $offset = ord($hash[19]) & 0x0F;

    $codigo =
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF);

    return str_pad((string) ($codigo % 1000000), 6, '0', STR_PAD_LEFT);
}

// Valida o código com tolerância de 30 segundos
function totp_validar(string $segredo, string $codigo): bool {
    $codigo = preg_replace('/\D/', '', $codigo);

    if (strlen($codigo) !== 6) {
        return false;
    }

    foreach ([-30, 0, 30] as $diferenca) {
        $esperado = totp_gerar_codigo($segredo, time() + $diferenca);

        if ($esperado !== '' && hash_equals($esperado, $codigo)) {
            return true;
        }
    }

    return false;
}
