<?php
session_start();
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

function lerXLSX($caminhoArquivo) {
    $zip = new ZipArchive;
    if ($zip->open($caminhoArquivo) !== true) {
        return false;
    }

    $sharedStrings = [];
    if (($xmlStrings = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
        $sxe = simplexml_load_string($xmlStrings);
        foreach ($sxe->si as $si) {
            $texto = '';
            if (isset($si->t)) {
                $texto = (string)$si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $r) {
                    $texto .= (string)$r->t;
                }
            }
            $sharedStrings[] = $texto;
        }
    }

    $xmlSheet = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($xmlSheet === false) {
        return false;
    }

    $sheet = simplexml_load_string($xmlSheet);
    $linhas = [];

    foreach ($sheet->sheetData->row as $row) {
        $linhaAtual = [];
        $ultimoIndice = -1;

        foreach ($row->c as $cell) {
            $ref = (string)$cell['r'];
            preg_match('/^([A-Z]+)/', $ref, $matches);
            $letras = $matches[1];
            $colIdx = 0;
            for ($i = 0; $i < strlen($letras); $i++) {
                $colIdx = $colIdx * 26 + (ord($letras[$i]) - 64);
            }
            $colIdx--;

            while ($ultimoIndice + 1 < $colIdx) {
                $linhaAtual[] = '';
                $ultimoIndice++;
            }

            $tipo = (string)$cell['t'];
            $valor = isset($cell->v) ? (string)$cell->v : '';

            if ($tipo === 's' && isset($sharedStrings[(int)$valor])) {
                $valor = $sharedStrings[(int)$valor];
            } elseif ($tipo === 'inlineStr' && isset($cell->is->t)) {
                $valor = (string)$cell->is->t;
            }

            $linhaAtual[] = trim($valor);
            $ultimoIndice = $colIdx;
        }
        $linhas[] = $linhaAtual;
    }

    return $linhas;
}

function lerCSV($caminhoArquivo) {
    $linhas = [];
    $conteudo = file_get_contents($caminhoArquivo);
    $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
    $primeiraLinha = strtok($conteudo, "\r\n");
    $delimitador = (substr_count($primeiraLinha, ';') >= substr_count($primeiraLinha, ',')) ? ';' : ',';

    $handle = fopen($caminhoArquivo, 'r');
    if ($handle !== false) {
        $primeira = true;
        while (($dados = fgetcsv($handle, 0, $delimitador)) !== false) {
            if ($primeira && isset($dados[0])) {
                $dados[0] = preg_replace('/^\xEF\xBB\xBF/', '', $dados[0]);
                $primeira = false;
            }
            $linhas[] = array_map('trim', $dados);
        }
        fclose($handle);
    }
    return $linhas;
}

function converterDataMySQL($valor, $comHora = false) {
    $valor = trim((string)$valor);
    if ($valor === '' || $valor === '-') return null;

    if (is_numeric($valor) && (float)$valor > 25569) {
        $unix = ((float)$valor - 25569) * 86400;
        return gmdate($comHora ? 'Y-m-d H:i:s' : 'Y-m-d', (int)round($unix));
    }

    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/', $valor, $m)) {
        $hora = isset($m[4]) ? "{$m[4]}:{$m[5]}:00" : "00:00:00";
        return $comHora ? "{$m[3]}-{$m[2]}-{$m[1]} {$hora}" : "{$m[3]}-{$m[2]}-{$m[1]}";
    }

    $ts = strtotime($valor);
    if ($ts !== false) {
        return date($comHora ? 'Y-m-d H:i:s' : 'Y-m-d', $ts);
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_planilha'])) {
    $arquivo = $_FILES['arquivo_planilha'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Erro ao enviar o arquivo.'); window.location.href='admin_animais.php';</script>";
        exit;
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $linhas = [];

    if ($extensao === 'csv') {
        $linhas = lerCSV($arquivo['tmp_name']);
    } elseif ($extensao === 'xlsx') {
        $linhas = lerXLSX($arquivo['tmp_name']);
    } else {
        echo "<script>alert('Formato inválido! Envie apenas arquivos .CSV ou .XLSX.'); window.location.href='admin_animais.php';</script>";
        exit;
    }

    if (!$linhas || count($linhas) < 2) {
        echo "<script>alert('A planilha está vazia ou contém apenas o cabeçalho.'); window.location.href='admin_animais.php';</script>";
        exit;
    }

    array_shift($linhas);

    $sql = "INSERT INTO Animais (data_entrada, endereco_origem, motivo_recolhimento, especie, sexo, castrado, raca, chip, pelagem, peso, data_nascimento, status_adocao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    $importados = 0;
    $ignorados = 0;

    foreach ($linhas as $col) {
        if (empty(array_filter($col))) continue;

        $especie = $col[3] ?? '';
        $sexo    = $col[4] ?? '';

        if ($especie === '' || $sexo === '') {
            $ignorados++;
            continue;
        }

        $data_entrada        = converterDataMySQL($col[0] ?? '', true) ?? date('Y-m-d H:i:s');
        $endereco_origem     = !empty($col[1]) ? $col[1] : null;
        $motivo_recolhimento = !empty($col[2]) ? $col[2] : null;
        
        $castrado_raw = mb_strtolower($col[5] ?? '0', 'UTF-8');
        $castrado     = in_array($castrado_raw, ['1', 'sim', 's', 'true', 'verdadeiro']) ? 1 : 0;

        $raca            = !empty($col[6]) ? $col[6] : null;
        $chip            = !empty($col[7]) ? preg_replace('/\D/', '', $col[7]) : null;
        $pelagem         = !empty($col[8]) ? $col[8] : null;
        
        $peso_raw        = str_replace(',', '.', $col[9] ?? '');
        $peso            = is_numeric($peso_raw) ? (float)$peso_raw : null;

        $data_nascimento = converterDataMySQL($col[10] ?? '', false);
        $status_adocao   = !empty($col[11]) ? $col[11] : 'Disponível';

        $stmt->bind_param("sssssisssdss",
            $data_entrada, $endereco_origem, $motivo_recolhimento, $especie, $sexo, $castrado,
            $raca, $chip, $pelagem, $peso, $data_nascimento, $status_adocao
        );

        if ($stmt->execute()) {
            $importados++;
        } else {
            $ignorados++;
        }
    }

    $stmt->close();
    $conexao->close();

    echo "<script>
        alert('Importação concluída!\\nRegistros inseridos: {$importados}\\nLinhas ignoradas: {$ignorados}');
        window.location.href='admin_animais.php';
    </script>";
    exit;
}
?>