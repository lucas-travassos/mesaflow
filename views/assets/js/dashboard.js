/**
 * Dashboard.JS - Interatividade do Dashboard
 * Gerencia: abrir/fechar mesas, atualizar status em tempo real, modais, etc
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard JS iniciado');

    // ===== DELEGAÇÃO DE EVENTOS PARA BOTÕES DE MESA =====
    const tablesGrid = document.getElementById('tables-grid');

    if (tablesGrid) {
        tablesGrid.addEventListener('click', function (e) {
            // Botão para Abrir Mesa
            if (e.target.closest('.btn-abrir')) {
                const mesaId = e.target.closest('.btn-abrir').dataset.mesaId;
                abrirMesa(mesaId);
            }

            // Botão para Fechar Mesa
            if (e.target.closest('.btn-fechar')) {
                const mesaId = e.target.closest('.btn-fechar').dataset.mesaId;
                fecharMesa(mesaId);
            }

            // Botão para Ver Pedido
            if (e.target.closest('.btn-pedido')) {
                const mesaId = e.target.closest('.btn-pedido').dataset.mesaId;
                abrirPedido(mesaId);
            }
        });
    }

    // ===== ATUALIZAÇÃO AUTOMÁTICA A CADA 5 SEGUNDOS =====
    setInterval(atualizarDashboard, 5000);

    console.log('Dashboard inicializado com sucesso');
});

/**
 * Abrir uma mesa (criar novo pedido)
 * Realiza requisição AJAX para atualizar o status para 'ocupada'
 * 
 * @param {int} mesaId ID da mesa
 */
function abrirMesa(mesaId) {
    if (!confirm('Tem certeza que deseja abrir a mesa ' + mesaId + '?')) {
        return;
    }

    const formData = new FormData();
    formData.append('mesa_id', mesaId);

    fetch('index.php?action=abrir_mesa', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarNotificacao('Mesa ' + mesaId + ' aberta com sucesso!', 'success');
            atualizarDashboard();
        } else {
            mostrarNotificacao('Erro ao abrir mesa: ' + data.erro, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        mostrarNotificacao('Erro ao comunicar com o servidor', 'error');
    });
}

/**
 * Fechar uma mesa (finalizar pedido)
 * Realiza requisição AJAX para atualizar o status para 'disponivel'
 * 
 * @param {int} mesaId ID da mesa
 */
function fecharMesa(mesaId) {
    if (!confirm('Tem certeza que deseja fechar a mesa ' + mesaId + '?')) {
        return;
    }

    const formData = new FormData();
    formData.append('mesa_id', mesaId);

    fetch('index.php?action=fechar_mesa', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarNotificacao('Mesa ' + mesaId + ' fechada com sucesso!', 'success');
            atualizarDashboard();
        } else {
            mostrarNotificacao('Erro ao fechar mesa: ' + data.erro, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        mostrarNotificacao('Erro ao comunicar com o servidor', 'error');
    });
}

/**
 * Abrir pedido da mesa (futuro: redirecionar para tela de pedidos)
 * 
 * @param {int} mesaId ID da mesa
 */
function abrirPedido(mesaId) {
    console.log('Abrindo pedido da mesa:', mesaId);
    mostrarNotificacao('Funcionalidade de pedidos em breve!', 'info');
    // TODO: Implementar navegação para tela de pedidos
    // window.location.href = 'index.php?action=pedidos&mesa_id=' + mesaId;
}

/**
 * Atualizar dashboard com dados do servidor
 * Requisição AJAX para buscar status atualizado das mesas
 */
function atualizarDashboard() {
    fetch('index.php?action=mesas_json')
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            // Atualizar totalizadores
            document.getElementById('total-mesas').textContent = data.totalizadores.total;
            document.getElementById('mesas-disponiveis').textContent = data.totalizadores.disponiveis;
            document.getElementById('mesas-ocupadas').textContent = data.totalizadores.ocupadas;
            document.getElementById('faturamento-total').textContent = 'R$ ' + formatarMoeda(data.totalizadores.faturamento_total);

            // Atualizar cards de mesa
            data.mesas.forEach(mesa => {
                atualizarCarteMesa(mesa);
            });
        }
    })
    .catch(error => console.error('Erro ao atualizar dashboard:', error));
}

/**
 * Atualizar visualmente um card de mesa individual
 * 
 * @param {object} mesa Dados da mesa do servidor
 */
function atualizarCarteMesa(mesa) {
    const cardElement = document.querySelector(`[data-mesa-id="${mesa.mesa_id}"]`);

    if (!cardElement) return;

    // Mudar classe de status
    cardElement.classList.remove('available', 'occupied', 'payment');
    
    if (mesa.status === 'disponivel') {
        cardElement.classList.add('available');
    } else {
        if (mesa.valor_total > 150) {
            cardElement.classList.add('payment');
        } else {
            cardElement.classList.add('occupied');
        }
    }

    // Atualizar conteúdo
    const content = geradorConteudoMesa(mesa);
    cardElement.innerHTML = content;
}

/**
 * Gerar HTML de conteúdo para um card de mesa
 * 
 * @param {object} mesa Dados da mesa
 * @return {string} HTML do conteúdo
 */
function geradorConteudoMesa(mesa) {
    let html = '';

    // Dot indicador (conta alta)
    if (mesa.status === 'ocupada' && mesa.valor_total > 150) {
        html += '<span class="small-dot" title="Conta alta"></span>';
    }

    // Número da mesa
    html += `<div class="table-number">${mesa.numero}</div>`;

    if (mesa.status === 'ocupada') {
        // Garçom responsável
        html += `<div class="table-seats">${mesa.garcom}</div>`;

        // Anel de tempo
        const progresso = calcularProgresso(mesa.tempo_minutos);
        html += `<div class="time-ring" style="--progress: ${progresso}%;">${mesa.tempo_formatado}</div>`;

        // Divisor
        html += '<div class="card-divider"></div>';

        // Conta
        html += `
            <div class="bill-row">
                <span class="bill-label">Conta Atual</span>
                <span class="bill-value">R$ ${formatarMoeda(mesa.valor_total)}</span>
            </div>
        `;
    } else {
        // Vazia
        html += '<div class="table-seats">Vazia</div>';
        html += '<div class="time-ring" style="--progress: 0%;">0m</div>';
        html += '<div class="card-divider"></div>';
        html += `
            <div class="status-row">
                <i class="fa-regular fa-circle-check"></i>
                <span>Disponível</span>
            </div>
        `;
    }

    // Botões de ação
    html += '<div class="card-actions">';
    
    if (mesa.status === 'disponivel') {
        html += `<button class="btn-action btn-abrir" data-mesa-id="${mesa.mesa_id}" title="Abrir mesa"><i class="fa-solid fa-door-open"></i></button>`;
    } else {
        html += `<button class="btn-action btn-fechar" data-mesa-id="${mesa.mesa_id}" title="Fechar mesa"><i class="fa-solid fa-door-closed"></i></button>`;
        html += `<button class="btn-action btn-pedido" data-mesa-id="${mesa.mesa_id}" title="Ver pedido"><i class="fa-solid fa-clipboard-list"></i></button>`;
    }

    html += '</div>';

    return html;
}

/**
 * Calcular percentual de progresso (0-100)
 * Assume que uma mesa lota em ~120 minutos
 * 
 * @param {int} minutos Tempo em minutos
 * @return {int} Percentual
 */
function calcularProgresso(minutos) {
    if (!minutos) return 0;
    
    const progresso = (minutos / 120) * 100;
    return Math.min(progresso, 100);
}

/**
 * Formatar número para moeda (BRL)
 * 
 * @param {float} valor Valor em reais
 * @return {string} Valor formatado
 */
function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Mostrar notificação temporária (toast)
 * 
 * @param {string} mensagem Mensagem a exibir
 * @param {string} tipo Tipo: success, error, info
 */
function mostrarNotificacao(mensagem, tipo = 'info') {
    // Criar elemento de notificação
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao notificacao-${tipo}`;
    notificacao.textContent = mensagem;

    // Estilos inline (para não precisar de CSS adicional)
    Object.assign(notificacao.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '16px 24px',
        background: tipo === 'success' ? 'rgba(76, 175, 80, 0.2)' : tipo === 'error' ? 'rgba(255, 100, 100, 0.2)' : 'rgba(70, 160, 255, 0.2)',
        color: tipo === 'success' ? '#4caf50' : tipo === 'error' ? '#ff6464' : '#46a0ff',
        border: `1px solid ${tipo === 'success' ? '#4caf50' : tipo === 'error' ? '#ff6464' : '#46a0ff'}`,
        borderRadius: '8px',
        zIndex: 9999,
        animation: 'slideIn 0.3s ease-out',
        fontWeight: '500',
        fontSize: '14px'
    });

    document.body.appendChild(notificacao);

    // Remover após 4 segundos
    setTimeout(() => {
        notificacao.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notificacao.remove(), 300);
    }, 4000);
}

/**
 * Animações de notificação (CSS-in-JS)
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

console.log('Dashboard JS carregado com sucesso');
