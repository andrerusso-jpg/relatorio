<?php
/**
 * Plugin Name: Relatório de Inscrições PRO
 */

add_shortcode('relatorio_inscricoes', function() {
    ob_start();
    ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <div class="container mt-4">
        <h2 class="mb-4">Dashboard PRO de Inscrições</h2>

        <!-- Filtros AJAX -->
        <form id="filtroForm" class="row g-3">
            <div class="col-md-3">
                <input type="date" name="inicio" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="fim" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="text" name="nome" placeholder="Nome" class="form-control">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">Status</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="pendente">Pendente</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </form>

        <hr>

        <div id="resultado"></div>

        <canvas id="grafico"></canvas>

    </div>

    <script>
    function carregarDados() {
        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            method: "POST",
            data: $('#filtroForm').serialize() + '&action=buscar_inscricoes',
            success: function(res) {
                $('#resultado').html(res.tabela);

                new Chart(document.getElementById('grafico'), {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Inscrições',
                            data: res.valores
                        }]
                    }
                });
            }
        });
    }

    $('#filtroForm input, #filtroForm select').on('change keyup', carregarDados);

    $(document).ready(carregarDados);
    </script>

    <?php
    return ob_get_clean();
});

// AJAX
add_action('wp_ajax_buscar_inscricoes', 'buscar_inscricoes');
add_action('wp_ajax_nopriv_buscar_inscricoes', 'buscar_inscricoes');

function buscar_inscricoes() {
    global $wpdb;

    $inicio = $_POST['inicio'] ?? '';
    $fim = $_POST['fim'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $status = $_POST['status'] ?? '';

    $query = "SELECT * FROM {$wpdb->prefix}inscricoes WHERE 1=1";

    if ($inicio && $fim) {
        $query .= $wpdb->prepare(" AND data_inscricao BETWEEN %s AND %s", $inicio, $fim);
    }

    if ($nome) {
        $query .= $wpdb->prepare(" AND nome LIKE %s", "%$nome%");
    }

    if ($status) {
        $query .= $wpdb->prepare(" AND status = %s", $status);
    }

    $dados = $wpdb->get_results($query);

    // Paginação simples
    $porPagina = 10;
    $pagina = $_POST['pagina'] ?? 1;
    $inicioPag = ($pagina - 1) * $porPagina;
    $dados = array_slice($dados, $inicioPag, $porPagina);

    // Tabela
    $tabela = '<table class="table table-bordered"><tr><th>ID</th><th>Nome</th><th>Email</th><th>Status</th><th>Data</th></tr>';
    foreach ($dados as $r) {
        $tabela .= "<tr>
            <td>{$r->id}</td>
            <td>{$r->nome}</td>
            <td>{$r->email}</td>
            <td>{$r->status}</td>
            <td>{$r->data_inscricao}</td>
        </tr>";
    }
    $tabela .= '</table>';

    // Gráfico
    $porData = [];
    foreach ($dados as $r) {
        $d = $r->data_inscricao;
        if (!isset($porData[$d])) $porData[$d] = 0;
        $porData[$d]++;
    }

    wp_send_json([
        'tabela' => $tabela,
        'labels' => array_keys($porData),
        'valores' => array_values($porData)
    ]);
}
