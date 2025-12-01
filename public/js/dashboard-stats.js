// Aguarda o DOM carregar completamente
document.addEventListener('DOMContentLoaded', function() {
    // Carrega todas as estatísticas
    loadDashboardStats();
});

/**
 * Função principal que carrega todas as estatísticas do dashboard
 */
async function loadDashboardStats() {
    await loadAdminsCount();
    // await loadVisitorsCount();
    // await loadProjectsCount();
    // await loadImagesCount();
}

/**
 * Busca a quantidade de administradores cadastrados
 */
async function loadAdminsCount() {
    try {
        const response = await fetch('/grupo_j.eldorado/public/admin/listar-admins', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        // Verifica se retornou um array de admins
        if (Array.isArray(data)) {
            const adminsCount = data.length;
            updateAdminsCard(adminsCount);
        } else if (data.error) {
            console.error('Erro ao buscar admins:', data.message);
            updateAdminsCard(0);
        } else {
            // Caso o formato seja diferente, tenta buscar um array dentro do objeto
            const adminsCount = data.data ? data.data.length : 0;
            updateAdminsCard(adminsCount);
        }

    } catch (error) {
        console.error('Erro ao carregar quantidade de admins:', error);
        updateAdminsCard(0);
        
        // Opcional: exibir mensagem de erro para o usuário
        showErrorNotification('Não foi possível carregar os dados de administradores');
    }
}

/**
 * Atualiza o card de administradores com o valor recebido
 * @param {number} count - Quantidade de administradores
 */
function updateAdminsCard(count) {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        const label = card.querySelector('.stat-label');
        if (label && label.textContent.trim() === 'Adms Ativos') {
            const numberElement = card.querySelector('.stat-number');
            if (numberElement) {
                numberElement.textContent = count;
            }
        }
    });
}