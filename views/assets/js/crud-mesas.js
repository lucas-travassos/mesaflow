/**
 * CRUD-MESAS.JS - Interatividade do Gerenciamento de Mesas
 * Gerencia: criar, editar, deletar mesas via AJAX
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('CRUD Mesas JS iniciado');

    const formMesa = document.getElementById('formMesa');
    const modalMesa = document.getElementById('modalMesa');
    const mesaIdInput = document.getElementById('mesaId');
    const numeroInput = document.getElementById('numero');
    const statusInput = document.getElementById('status');
    const modalTitle = document.getElementById('modalTitle');

    let mesaEmEdicao = null;

    // ===== EVENTO: ABRIR MODAL DE NOVA MESA =====
    const btnNovaMesa = document.getElementById('btn-nova-mesa');
    if (btnNovaMesa) {
        btnNovaMesa.addEventListener('click', function () {
            limparFormulario();
            mesaEmEdicao = null;
            modalTitle.textContent = 'Nova Mesa';
        });
    }

    // ===== EVENTO: EDITAR MESA =====
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function () {
            const mesaId = this.dataset.mesaId;
            const numero = this.dataset.numero;
            const status = this.dataset.status;

            // Preencher formulário com dados da mesa
            mesaIdInput.value = mesaId;
            numeroInput.value = numero;
            statusInput.value = status;

            mesaEmEdicao = mesaId;
            modalTitle.textContent = 'Editar Mesa #' + numero;
        });
    });

    // ===== EVENTO: SUBMIT DO FORMULÁRIO =====
    formMesa.addEventListener('submit', function (e) {
        e.preventDefault();

        const numero = parseInt(numeroInput.value);
        const status = statusInput.value;

        if (!numero || numero <= 0) {
            mostrarNotificacao('Número da mesa inválido', 'error');
            return;
        }

        if (mesaEmEdicao) {
            // Modo: EDITAR
            editarMesa(mesaEmEdicao, numero, status);
        } else {
            // Modo: CRIAR
            criarMesa(numero, status);
        }
    });

    // ===== EVENTO: DELETAR MESA =====
    document.querySelectorAll('.btn-deletar').forEach(btn => {
        btn.addEventListener('click', function () {
            const mesaId = this.dataset.mesaId;
            const numeroMesa = this.closest('tr').querySelector('strong').textContent;

            if (confirm(`Tem certeza que deseja deletar a mesa ${numeroMesa}?`)) {
                deletarMesa(mesaId);
            }
        });
    });

    console.log('CRUD Mesas inicializado com sucesso');
});

/**
 * Criar uma nova mesa
 * 
 * @param {int} numero Número da mesa
 * @param {string} status Status da mesa
 */
function criarMesa(numero, status) {
    const formData = new FormData();
    formData.append('numero', numero);
    formData.append('status', status);

    fetch('index.php?action=criar_mesa', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarNotificacao('Mesa #' + numero + ' criada com sucesso!', 'success');
            
            // Fechar modal
            const modalElement = document.getElementById('modalMesa');
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();

            // Recarregar tabela
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacao('Erro ao criar mesa: ' + data.erro, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        mostrarNotificacao('Erro ao comunicar com o servidor', 'error');
    });
}

/**
 * Editar uma mesa existente
 * 
 * @param {int} mesaId ID da mesa
 * @param {int} numero Número da mesa
 * @param {string} status Status da mesa
 */
function editarMesa(mesaId, numero, status) {
    const formData = new FormData();
    formData.append('mesa_id', mesaId);
    formData.append('numero', numero);
    formData.append('status', status);

    fetch('index.php?action=atualizar_mesa', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarNotificacao('Mesa #' + numero + ' atualizada com sucesso!', 'success');
            
            // Fechar modal
            const modalElement = document.getElementById('modalMesa');
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();

            // Recarregar tabela
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacao('Erro ao atualizar mesa: ' + data.erro, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        mostrarNotificacao('Erro ao comunicar com o servidor', 'error');
    });
}

/**
 * Deletar uma mesa
 * 
 * @param {int} mesaId ID da mesa
 */
function deletarMesa(mesaId) {
    const formData = new FormData();
    formData.append('mesa_id', mesaId);

    fetch('index.php?action=deletar_mesa', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarNotificacao('Mesa deletada com sucesso!', 'success');
            
            // Remover linha da tabela
            const row = document.querySelector(`tr[data-mesa-id="${mesaId}"]`);
            if (row) {
                row.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => row.remove(), 300);
            }

            // Atualizar total
            atualizarTotal();
        } else {
            mostrarNotificacao('Erro ao deletar mesa: ' + data.erro, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        mostrarNotificacao('Erro ao comunicar com o servidor', 'error');
    });
}

/**
 * Limpar formulário
 */
function limparFormulario() {
    document.getElementById('formMesa').reset();
    document.getElementById('mesaId').value = '';
    document.getElementById('numero').focus();
}

/**
 * Atualizar total de mesas
 */
function atualizarTotal() {
    fetch('index.php?action=listar_mesas_json')
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            document.getElementById('total-mesas').textContent = data.total;
        }
    })
    .catch(error => console.error('Erro ao atualizar total:', error));
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

    // Estilos inline
    const bgColor = tipo === 'success' ? 'rgba(76, 175, 80, 0.2)' : tipo === 'error' ? 'rgba(255, 100, 100, 0.2)' : 'rgba(70, 160, 255, 0.2)';
    const textColor = tipo === 'success' ? '#4caf50' : tipo === 'error' ? '#ff6464' : '#46a0ff';
    const borderColor = tipo === 'success' ? '#4caf50' : tipo === 'error' ? '#ff6464' : '#46a0ff';

    Object.assign(notificacao.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '16px 24px',
        background: bgColor,
        color: textColor,
        border: `1px solid ${borderColor}`,
        borderRadius: '8px',
        zIndex: 9999,
        animation: 'slideIn 0.3s ease-out',
        fontWeight: '500',
        fontSize: '14px',
        maxWidth: '400px',
        wordWrap: 'break-word'
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

console.log('CRUD Mesas JS carregado com sucesso');
