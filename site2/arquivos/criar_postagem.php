<?php
require_once 'session.php';
require_once 'conexao.php';

// Apenas professores podem postar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'professor') {
    header("Location: dashboard.php");
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $tipo_postagem = isset($_POST['tipo_postagem']) ? trim($_POST['tipo_postagem']) : '';
    $tipo_questao = isset($_POST['tipo_questao']) ? trim($_POST['tipo_questao']) : '';
    $materias = isset($_POST['materias']) ? $_POST['materias'] : [];
    $obrigatoria = isset($_POST['obrigatoria']) ? 1 : 0;

    // Validação básica
    if (empty($titulo)) {
        $erro = 'Título é obrigatório.';
    } elseif (strlen($titulo) < 5) {
        $erro = 'Título deve ter no mínimo 5 caracteres.';
    } elseif (!in_array($tipo_postagem, ['questao', 'arquivo'])) {
        $erro = 'Tipo de postagem inválido.';
    } elseif (empty($materias)) {
        $erro = 'Selecione pelo menos uma matéria.';
    } else {
        try {
            $arquivo_path = null;
            $enunciado = null;
            $alternativas_json = null;
            $resposta_correta = null;
            $conteudo = null;

            if ($tipo_postagem === 'arquivo') {
                // Validar upload de arquivo
                if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Arquivo não foi enviado ou erro no upload.');
                }

                $arquivo = $_FILES['arquivo'];
                $extensoes_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'jpg', 'png', 'gif'];
                $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

                if (!in_array($extensao, $extensoes_permitidas)) {
                    throw new Exception('Tipo de arquivo não permitido.');
                }

                if ($arquivo['size'] > 50 * 1024 * 1024) {
                    throw new Exception('Arquivo muito grande. Máximo 50MB.');
                }

                $upload_dir = __DIR__ . '/uploads/postagens/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $nome_arquivo = uniqid('post_') . '_' . basename($arquivo['name']);
                $caminho_arquivo = $upload_dir . $nome_arquivo;

                if (!move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
                    throw new Exception('Erro ao salvar o arquivo no servidor.');
                }

                $arquivo_path = 'uploads/postagens/' . $nome_arquivo;
                $conteudo = 'Arquivo de postagem';
                $tipo_questao = null;

            } else if ($tipo_postagem === 'questao') {
                if (!in_array($tipo_questao, ['discursiva', 'objetiva'])) {
                    throw new Exception('Tipo de questão inválido.');
                }

                $enunciado = isset($_POST['enunciado']) ? trim($_POST['enunciado']) : '';
                
                if (empty($enunciado)) {
                    throw new Exception('Enunciado é obrigatório.');
                }
                if (strlen($enunciado) < 10) {
                    throw new Exception('Enunciado deve ter no mínimo 10 caracteres.');
                }

                $conteudo = 'Questão ' . ($tipo_questao === 'discursiva' ? 'discursiva' : 'objetiva');

                if ($tipo_questao === 'objetiva') {
                    $num_alternativas = isset($_POST['num_alternativas']) ? intval($_POST['num_alternativas']) : 0;
                    
                    if ($num_alternativas < 2 || $num_alternativas > 10) {
                        throw new Exception('Número de alternativas deve estar entre 2 e 10.');
                    }

                    $alternativas = [];
                    for ($i = 0; $i < $num_alternativas; $i++) {
                        $alt = isset($_POST['alternativa_' . $i]) ? trim($_POST['alternativa_' . $i]) : '';
                        if (empty($alt)) {
                            throw new Exception('Todas as alternativas devem ser preenchidas.');
                        }
                        $alternativas[] = $alt;
                    }

                    $resposta_idx = isset($_POST['resposta_correta']) ? intval($_POST['resposta_correta']) : -1;
                    if ($resposta_idx < 0 || $resposta_idx >= $num_alternativas) {
                        throw new Exception('Selecione uma resposta correta válida.');
                    }

                    $alternativas_json = json_encode([
                        'alternativas' => $alternativas,
                        'resposta_correta' => $resposta_idx
                    ]);
                    $resposta_correta = strval($resposta_idx);
                }
            }

            // Converter array de matérias em string
            $materias_str = implode(',', $materias);

            // Inserir postagem no banco
            $stmt = $pdo->prepare("
                INSERT INTO postagens (
                    professor_id, titulo, tipo_postagem, tipo_questao, conteudo,
                    arquivo_path, enunciado, alternativas_json, resposta_correta,
                    obrigatoria, materias
                ) VALUES (
                    :professor_id, :titulo, :tipo_postagem, :tipo_questao, :conteudo,
                    :arquivo_path, :enunciado, :alternativas_json, :resposta_correta,
                    :obrigatoria, :materias
                )
            ");

            $stmt->execute([
                ':professor_id' => $_SESSION['usuario_id'],
                ':titulo' => $titulo,
                ':tipo_postagem' => $tipo_postagem,
                ':tipo_questao' => $tipo_questao,
                ':conteudo' => $conteudo,
                ':arquivo_path' => $arquivo_path,
                ':enunciado' => $enunciado,
                ':alternativas_json' => $alternativas_json,
                ':resposta_correta' => $resposta_correta,
                ':obrigatoria' => $obrigatoria,
                ':materias' => $materias_str
            ]);

            $sucesso = 'Postagem criada com sucesso!';
            
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
            </script>";

        } catch (Exception $e) {
            $erro = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            error_log('Erro criar_postagem.php: ' . $e->getMessage());
        }
    }
}

$materias_disponiveis = [
    'Matemática' => 'matematica',
    'Português' => 'portugues',
    'História' => 'historia',
    'Geografia' => 'geografia',
    'Ciências' => 'ciencias',
    'Biologia' => 'biologia',
    'Física' => 'fisica',
    'Química' => 'quimica',
    'Inglês' => 'ingles',
    'Educação Física' => 'educacao_fisica'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Nova Postagem — EduShare</title>
</head>
<body class="bg-blue-50 text-gray-800">
    <main class="min-h-screen py-12">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-3xl shadow-md overflow-hidden">
                <!-- Cabeçalho -->
                <div class="bg-blue-50 border-b-2 border-blue-500 px-8 py-6">
                    <h1 class="text-3xl font-bold text-blue-600 mb-2">Nova Postagem</h1>
                    <p class="text-gray-600">Crie uma questão ou compartilhe um arquivo</p>
                </div>

                <!-- Alertas -->
                <?php if ($erro): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 m-6 rounded-lg">
                        <strong>Erro:</strong> <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 m-6 rounded-lg">
                        <strong>Sucesso:</strong> <?php echo $sucesso; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="formPostagem" class="p-8 space-y-8">
                    <!-- Título -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Título <span class="text-red-500">*</span></label>
                        <input type="text" name="titulo" placeholder="Ex: Exercícios de Álgebra" required 
                            class="w-full p-4 rounded-2xl border border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none transition">
                    </div>

                    <!-- Tipo de Postagem -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-3">Tipo <span class="text-red-500">*</span></label>
                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="tipo_postagem" value="questao" checked onchange="atualizarTipo()" 
                                    class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                                <span class="ml-3 text-gray-700">Questão</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="tipo_postagem" value="arquivo" onchange="atualizarTipo()" 
                                    class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                                <span class="ml-3 text-gray-700">Upload de Arquivo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Seção Questão -->
                    <div id="secaoQuestao" class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-3">Tipo de Questão <span class="text-red-500">*</span></label>
                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_questao" value="discursiva" checked onchange="atualizarTipoQuestao()" 
                                        class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                                    <span class="ml-3 text-gray-700">Discursiva (resposta aberta)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_questao" value="objetiva" onchange="atualizarTipoQuestao()" 
                                        class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                                    <span class="ml-3 text-gray-700">Objetiva (múltipla escolha)</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Enunciado <span class="text-red-500">*</span></label>
                            <textarea name="enunciado" rows="5" placeholder="Digite a pergunta..." required 
                                class="w-full p-4 rounded-2xl border border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none transition resize-none"></textarea>
                        </div>

                        <!-- Alternativas -->
                        <div id="secaoAlternativas" class="hidden space-y-4 p-6 bg-gray-50 rounded-2xl">
                            <label class="block text-gray-700 font-semibold">Número de Alternativas <span class="text-red-500">*</span></label>
                            <select name="num_alternativas" onchange="atualizarAlternativas()" 
                                class="w-full p-4 rounded-2xl border border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">
                                <option value="2">2 alternativas</option>
                                <option value="3">3 alternativas</option>
                                <option value="4" selected>4 alternativas</option>
                                <option value="5">5 alternativas</option>
                                <option value="6">6 alternativas</option>
                                <option value="7">7 alternativas</option>
                                <option value="8">8 alternativas</option>
                                <option value="9">9 alternativas</option>
                                <option value="10">10 alternativas</option>
                            </select>
                            
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Alternativas <span class="text-red-500">*</span></label>
                                <div id="containerAlternativas" class="space-y-2"></div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-3">Resposta Correta <span class="text-red-500">*</span></label>
                                <div id="containerRespostas" class="space-y-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção Arquivo -->
                    <div id="secaoArquivo" class="hidden">
                        <label class="block text-gray-700 font-semibold mb-4">Arquivo <span class="text-red-500">*</span></label>
                        <div id="dropZone" class="border-2 border-dashed border-blue-300 rounded-2xl p-8 text-center cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                            <input type="file" name="arquivo" id="inputArquivo" class="hidden">
                            <p class="font-semibold text-gray-700 mb-2">Arraste ou clique</p>
                            <p class="text-sm text-gray-500">PDF, DOC, ZIP, Imagens (Máx: 50MB)</p>
                        </div>
                        <div id="nomeArquivo" class="mt-4"></div>
                    </div>

                    <!-- Obrigatoriedade -->
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="obrigatoria" 
                                class="w-5 h-5 text-blue-500 accent-blue-500 rounded cursor-pointer">
                            <span class="ml-3 text-gray-700">Esta postagem é obrigatória?</span>
                        </label>
                    </div>

                    <!-- Matérias -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-3">Matérias <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <?php foreach ($materias_disponiveis as $nome => $valor): ?>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="materias[]" value="<?php echo $valor; ?>" 
                                        class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                                    <span class="ml-2 text-gray-700"><?php echo $nome; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-3xl shadow transition">
                            Publicar
                        </button>
                        <a href="dashboard.php" class="flex-1 bg-white hover:bg-gray-50 border border-blue-500 text-blue-500 font-bold py-3 rounded-3xl shadow transition text-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const dropZone = document.getElementById('dropZone');
        const inputArquivo = document.getElementById('inputArquivo');

        function atualizarTipo() {
            const tipo = document.querySelector('input[name="tipo_postagem"]:checked').value;
            document.getElementById('secaoQuestao').classList.toggle('hidden', tipo === 'arquivo');
            document.getElementById('secaoArquivo').classList.toggle('hidden', tipo === 'questao');
        }

        function atualizarTipoQuestao() {
            const tipo = document.querySelector('input[name="tipo_questao"]:checked').value;
            document.getElementById('secaoAlternativas').classList.toggle('hidden', tipo === 'discursiva');
            if (tipo === 'objetiva') atualizarAlternativas();
        }

        function atualizarAlternativas() {
            const num = parseInt(document.querySelector('select[name="num_alternativas"]').value);
            const container = document.getElementById('containerAlternativas');
            const respostas = document.getElementById('containerRespostas');
            
            container.innerHTML = '';
            respostas.innerHTML = '';

            for (let i = 0; i < num; i++) {
                const altDiv = document.createElement('div');
                altDiv.className = 'flex items-center gap-2';
                const label = String.fromCharCode(65 + i);
                altDiv.innerHTML = `
                    <span class="font-bold text-blue-500 w-6">${label}</span>
                    <input type="text" name="alternativa_${i}" placeholder="Opção ${label}" required
                        class="flex-1 p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none">
                `;
                container.appendChild(altDiv);

                const respDiv = document.createElement('label');
                respDiv.className = 'flex items-center cursor-pointer';
                respDiv.innerHTML = `
                    <input type="radio" name="resposta_correta" value="${i}" ${i === 0 ? 'checked' : ''} 
                        class="w-5 h-5 text-blue-500 accent-blue-500 cursor-pointer">
                    <span class="ml-2 text-gray-700">Opção ${label}</span>
                `;
                respostas.appendChild(respDiv);
            }
        }

        dropZone.addEventListener('click', () => inputArquivo.click());
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-blue-50', 'border-blue-400');
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-blue-50', 'border-blue-400');
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-blue-50', 'border-blue-400');
            if (e.dataTransfer.files.length > 0) {
                inputArquivo.files = e.dataTransfer.files;
                if (e.dataTransfer.files[0]) {
                    const tamanho = (e.dataTransfer.files[0].size / 1024 / 1024).toFixed(2);
                    document.getElementById('nomeArquivo').innerHTML = `<p class="text-green-600 font-semibold">✓ ${e.dataTransfer.files[0].name} (${tamanho} MB)</p>`;
                }
            }
        });
        inputArquivo.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                const tamanho = (e.target.files[0].size / 1024 / 1024).toFixed(2);
                document.getElementById('nomeArquivo').innerHTML = `<p class="text-green-600 font-semibold">✓ ${e.target.files[0].name} (${tamanho} MB)</p>`;
            }
        });

        document.getElementById('formPostagem').addEventListener('submit', function(e) {
            const tipo = document.querySelector('input[name="tipo_postagem"]:checked').value;
            if (tipo === 'arquivo' && !inputArquivo.files.length) {
                e.preventDefault();
                alert('Selecione um arquivo');
                return false;
            }
            const materias = document.querySelectorAll('input[name="materias[]"]:checked');
            if (materias.length === 0) {
                e.preventDefault();
                alert('Selecione pelo menos uma matéria');
                return false;
            }
        });

        atualizarAlternativas();
    </script>
</body>
</html>
