<?php
session_start();
if (!isset($_SESSION['loggedin']) || ($_SESSION['nivel_acesso'] != 'admin' && $_SESSION['nivel_acesso'] != 'master')) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Animais - Painel Estilo Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .excel-wrapper {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 20px;
            margin: 25px auto;
            max-width: 98%;
        }
        .table-scroll {
            overflow-x: auto;
            max-height: 70vh;
            border: 1px solid #d0d7de;
        }
        table.excel-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.88rem;
        }
        table.excel-grid th,
        table.excel-grid td {
            border: 1px solid #d0d7de;
            padding: 6px 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        table.excel-grid thead th {
            background-color: #f39c12;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
            user-select: none;
            cursor: pointer;
        }
        table.excel-grid thead tr.filter-row th {
            background-color: #f8f9fa;
            top: 34px;
            z-index: 2;
            padding: 4px;
            cursor: default;
        }
        table.excel-grid thead tr.filter-row input,
        table.excel-grid thead tr.filter-row select {
            width: 100%;
            font-size: 0.8rem;
            padding: 2px 5px;
            border: 1px solid #ced4da;
            border-radius: 3px;
        }
        table.excel-grid tbody tr:nth-child(even) {
            background-color: #f9fbfc;
        }
        table.excel-grid tbody tr:hover {
            background-color: #eef6ff;
        }
        .resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 6px;
            cursor: col-resize;
            user-select: none;
            height: 100%;
        }
        .resizer:hover, .resizing {
            background-color: rgba(0, 0, 0, 0.35);
        }
        .thumb-animal {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="excel-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="fw-bold mb-0" style="color: #e67e22;">Planilha de Animais Cadastrados</h3>
            <small class="text-muted">Arraste a borda das colunas para redimensionar • Clique no cabeçalho para ordenar • Use os filtros abaixo</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="buscaGlobal" class="form-control form-control-sm" style="width: 220px;" placeholder="Busca rápida em todas colunas...">
            <button type="button" id="btnLimparFiltros" class="btn btn-outline-secondary btn-sm">Limpar Filtros</button>
            <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportar">Importar CSV / XLSX</button>
            <button type="button" id="btnExportarCSV" class="btn btn-success btn-sm">Exportar Excel (.CSV)</button>
            <a href="cadastro_animal.php" class="btn btn-primary btn-sm fw-bold">+ Novo Animal</a>
            <a href="index.php" class="btn btn-dark btn-sm">Home</a>
        </div>
    </div>

    <div class="table-scroll">
        <table class="excel-grid" id="tabelaAnimais">
            <thead>
                <tr class="header-row">
                    <th style="width: 55px;" data-col="0">ID ⇅<div class="resizer"></div></th>
                    <th style="width: 65px;" data-col="1">Foto<div class="resizer"></div></th>
                    <th style="width: 100px;" data-col="2">Espécie ⇅<div class="resizer"></div></th>
                    <th style="width: 120px;" data-col="3">Raça ⇅<div class="resizer"></div></th>
                    <th style="width: 85px;" data-col="4">Sexo ⇅<div class="resizer"></div></th>
                    <th style="width: 90px;" data-col="5">Castrado ⇅<div class="resizer"></div></th>
                    <th style="width: 85px;" data-col="6">Peso (kg) ⇅<div class="resizer"></div></th>
                    <th style="width: 110px;" data-col="7">Pelagem ⇅<div class="resizer"></div></th>
                    <th style="width: 135px;" data-col="8">Chip ⇅<div class="resizer"></div></th>
                    <th style="width: 140px;" data-col="9">Status ⇅<div class="resizer"></div></th>
                    <th style="width: 135px;" data-col="10">Data Entrada ⇅<div class="resizer"></div></th>
                    <th style="width: 160px;" data-col="11">Origem ⇅<div class="resizer"></div></th>
                    <th style="width: 135px;" data-col="12">Ações<div class="resizer"></div></th>
                </tr>
                <tr class="filter-row">
                    <th><input type="text" class="col-filter" data-col="0" placeholder="ID"></th>
                    <th>
                        <select class="col-filter" data-col="1">
                            <option value="">Todas</option>
                            <option value="Com foto">Com foto</option>
                            <option value="Sem foto">Sem foto</option>
                        </select>
                    </th>
                    <th><input type="text" class="col-filter" data-col="2" placeholder="Filtrar..."></th>
                    <th><input type="text" class="col-filter" data-col="3" placeholder="Filtrar..."></th>
                    <th>
                        <select class="col-filter" data-col="4">
                            <option value="">Todos</option>
                            <option value="Macho">Macho</option>
                            <option value="Fêmea">Fêmea</option>
                        </select>
                    </th>
                    <th>
                        <select class="col-filter" data-col="5">
                            <option value="">Todos</option>
                            <option value="Sim">Sim</option>
                            <option value="Não">Não</option>
                        </select>
                    </th>
                    <th><input type="text" class="col-filter" data-col="6" placeholder="Peso"></th>
                    <th><input type="text" class="col-filter" data-col="7" placeholder="Filtrar..."></th>
                    <th><input type="text" class="col-filter" data-col="8" placeholder="Chip"></th>
                    <th>
                        <select class="col-filter" data-col="9">
                            <option value="">Todos</option>
                            <option value="Disponível">Disponível</option>
                            <option value="Em processo de adoção">Em processo</option>
                            <option value="Adotado">Adotado</option>
                        </select>
                    </th>
                    <th><input type="text" class="col-filter" data-col="10" placeholder="dd/mm/aaaa"></th>
                    <th><input type="text" class="col-filter" data-col="11" placeholder="Filtrar..."></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'conexao.php';
                $sql = "SELECT * FROM Animais ORDER BY data_entrada DESC";
                $result = $conexao->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $castrado_txt = ($row["castrado"] == 1) ? "Sim" : "Não";
                        $tem_foto = (!empty($row["foto"]) && file_exists($row["foto"]));
                        
                        $badge_class = 'bg-secondary';
                        if ($row["status_adocao"] == 'Disponível') $badge_class = 'bg-success';
                        elseif ($row["status_adocao"] == 'Em processo de adoção') $badge_class = 'bg-warning text-dark';
                        elseif ($row["status_adocao"] == 'Adotado') $badge_class = 'bg-info text-dark';

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                        echo "<td class='text-center'>";
                        if ($tem_foto) {
                            echo "<img src='" . htmlspecialchars($row["foto"]) . "' class='thumb-animal' alt='Com foto' title='Clique para ampliar' onclick='abrirFoto(\"" . htmlspecialchars($row["foto"]) . "\")'>";
                            echo "<span class='d-none'>Com foto</span>";
                        } else {
                            echo "<span class='text-muted small'>Sem foto</span>";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row["especie"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["raca"] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($row["sexo"]) . "</td>";
                        echo "<td>" . $castrado_txt . "</td>";
                        echo "<td>" . ($row["peso"] !== null ? number_format((float)$row["peso"], 2, ',', '.') : '-') . "</td>";
                        echo "<td>" . htmlspecialchars($row["pelagem"] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($row["chip"] ?? '-') . "</td>";
                        echo "<td><span class='badge $badge_class'>" . htmlspecialchars($row["status_adocao"]) . "</span></td>";
                        echo "<td>" . date("d/m/Y H:i", strtotime($row["data_entrada"])) . "</td>";
                        echo "<td title='" . htmlspecialchars($row["endereco_origem"] ?? '') . "'>" . htmlspecialchars($row["endereco_origem"] ?? '-') . "</td>";
                        echo "<td class='text-center'>";
                        echo "<a href='editar_animal.php?id=" . $row["id"] . "' class='btn btn-sm btn-outline-primary py-0 px-2 me-1'>Editar</a>";
                        if ($_SESSION['nivel_acesso'] == 'master') {
                            echo "<a href='excluir_animal.php?id=" . $row["id"] . "' class='btn btn-sm btn-outline-danger py-0 px-2' onclick='return confirm(\"Tem certeza que deseja excluir este animal?\");'>Excluir</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr id='semRegistros'><td colspan='13' class='text-center py-3'>Nenhum animal cadastrado.</td></tr>";
                }
                $conexao->close();
                ?>
            </tbody>
        </table>
    </div>
    <div class="mt-2 d-flex justify-content-between small text-muted">
        <span id="contadorLinhas"></span>
    </div>
</div>

<!-- Modal para ampliar foto do animal -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Foto do Animal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imgModalAmpliada" src="" class="img-fluid rounded" alt="Foto do Animal">
            </div>
        </div>
    </div>
</div>

<!-- Modal de Importação em Massa (CSV / XLSX) -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="importar_animais.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Importar Animais em Massa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">
                        Envie um arquivo <strong>.CSV</strong> ou <strong>.XLSX</strong> seguindo a ordem das colunas da planilha modelo:
                    </p>
                    <div class="alert alert-light border small p-2 mb-3">
                        <code>data_entrada | endereco_origem | motivo_recolhimento | especie | sexo | castrado | raca | chip | pelagem | peso | data_nascimento | status_adocao</code>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="btnBaixarModelo" class="btn btn-sm btn-outline-primary">
                            Baixar Planilha Modelo (.CSV)
                        </button>
                    </div>

                    <div class="mb-2">
                        <label for="arquivo_planilha" class="form-label fw-bold small">Selecione o arquivo (.csv ou .xlsx):</label>
                        <input type="file" name="arquivo_planilha" id="arquivo_planilha" class="form-control" accept=".csv, .xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Iniciar Importação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('tabelaAnimais');
    const tbody = table.querySelector('tbody');
    const colFilters = document.querySelectorAll('.col-filter');
    const buscaGlobal = document.getElementById('buscaGlobal');
    const contador = document.getElementById('contadorLinhas');

    // 1. REDIMENSIONAMENTO DE COLUNAS ESTILO EXCEL
    const headers = table.querySelectorAll('thead tr.header-row th');
    headers.forEach((th) => {
        const resizer = th.querySelector('.resizer');
        if (!resizer) return;

        let startX, startWidth;

        resizer.addEventListener('mousedown', function (e) {
            e.stopPropagation();
            startX = e.pageX;
            startWidth = th.offsetWidth;
            resizer.classList.add('resizing');

            const onMouseMove = (e) => {
                const newWidth = Math.max(45, startWidth + (e.pageX - startX));
                th.style.width = newWidth + 'px';
            };

            const onMouseUp = () => {
                resizer.classList.remove('resizing');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });

    // 2. FILTROS POR COLUNA + BUSCA GLOBAL
    function aplicarFiltros() {
        const rows = tbody.querySelectorAll('tr:not(#semRegistros)');
        const globalTerm = buscaGlobal.value.toLowerCase().trim();
        let visiveis = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let mostrar = true;

            if (globalTerm && !row.innerText.toLowerCase().includes(globalTerm)) {
                mostrar = false;
            }

            if (mostrar) {
                colFilters.forEach(filter => {
                    const val = filter.value.toLowerCase().trim();
                    if (val !== '') {
                        const colIdx = parseInt(filter.getAttribute('data-col'));
                        const cellText = cells[colIdx].innerText.toLowerCase();
                        if (!cellText.includes(val)) {
                            mostrar = false;
                        }
                    }
                });
            }

            row.style.display = mostrar ? '' : 'none';
            if (mostrar) visiveis++;
        });

        contador.textContent = `Exibindo ${visiveis} de ${rows.length} registro(s)`;
    }

    colFilters.forEach(f => {
        f.addEventListener('input', aplicarFiltros);
        f.addEventListener('change', aplicarFiltros);
    });
    buscaGlobal.addEventListener('input', aplicarFiltros);

    document.getElementById('btnLimparFiltros').addEventListener('click', () => {
        buscaGlobal.value = '';
        colFilters.forEach(f => f.value = '');
        aplicarFiltros();
    });

    // 3. ORDENAÇÃO AO CLICAR NO CABEÇALHO
    let sortDirection = {};
    headers.forEach((th) => {
        th.addEventListener('click', (e) => {
            if (e.target.classList.contains('resizer')) return;
            const colIdx = parseInt(th.getAttribute('data-col'));
            if (colIdx === 1 || colIdx === 12) return;

            const rows = Array.from(tbody.querySelectorAll('tr:not(#semRegistros)'));
            const asc = !sortDirection[colIdx];
            sortDirection[colIdx] = asc;

            rows.sort((a, b) => {
                let textA = a.children[colIdx].innerText.trim();
                let textB = b.children[colIdx].innerText.trim();

                if (colIdx === 0 || colIdx === 6) {
                    const numA = parseFloat(textA.replace(',', '.')) || 0;
                    const numB = parseFloat(textB.replace(',', '.')) || 0;
                    return asc ? numA - numB : numB - numA;
                }
                return asc ? textA.localeCompare(textB, 'pt-BR') : textB.localeCompare(textA, 'pt-BR');
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // 4. EXPORTAR LINHAS VISÍVEIS PARA CSV
    document.getElementById('btnExportarCSV').addEventListener('click', () => {
        const csv = [];
        const headerTitles = Array.from(headers).slice(0, 12).map(th => `"${th.innerText.replace('⇅', '').trim()}"`);
        csv.push(headerTitles.join(';'));

        const rows = tbody.querySelectorAll('tr:not(#semRegistros)');
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const cols = Array.from(row.querySelectorAll('td')).slice(0, 12).map(td => `"${td.innerText.trim().replace(/"/g, '""')}"`);
            csv.push(cols.join(';'));
        });

        const blob = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'animais_ccz_' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });

    // 5. GERAR E BAIXAR PLANILHA MODELO PARA IMPORTAÇÃO
    document.getElementById('btnBaixarModelo').addEventListener('click', () => {
        const cabecalho = [
            'data_entrada', 'endereco_origem', 'motivo_recolhimento', 'especie',
            'sexo', 'castrado', 'raca', 'chip', 'pelagem', 'peso', 'data_nascimento', 'status_adocao'
        ];
        const exemplo = [
            '22/11/2026 14:30', 'Rua das Flores, 120 - Centro', 'Resgate via pública', 'Cachorro',
            'Macho', 'Sim', 'SRD', '985112004567891', 'Caramelo', '12,50', '10/05/2024', 'Disponível'
        ];
        const conteudo = "\uFEFF" + cabecalho.join(';') + "\n" + exemplo.join(';');
        const blob = new Blob([conteudo], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'modelo_importacao_animais.csv';
        link.click();
    });

    aplicarFiltros();
});

function abrirFoto(src) {
    document.getElementById('imgModalAmpliada').src = src;
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
</script>
</body>
</html>