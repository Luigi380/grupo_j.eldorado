// Script para gerenciar o dashboard do admin
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
});

/**
 * Função para realizar logout
 */
async function handleLogout() {
    // Confirmação antes de sair
    if (!confirm('Deseja realmente sair?')) {
        return;
    }
    
    const logoutBtn = document.getElementById('logoutBtn');
    const originalHtml = logoutBtn.innerHTML;
    
    // Desabilita o botão e mostra feedback
    logoutBtn.disabled = true;
    logoutBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saindo...';
    
    try {
        const response = await fetch('/grupo_j.eldorado/public/admin/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.error) {
            // Se houver erro no logout
            alert('Erro ao fazer logout: ' + data.message);
            logoutBtn.disabled = false;
            logoutBtn.innerHTML = originalHtml;
            return;
        }
        
        // Logout bem-sucedido
        console.log('Logout realizado com sucesso');
        
        // Redireciona para a página de login
        window.location.href = '/grupo_j.eldorado/public/admin/login';
        
    } catch (error) {
        console.error('Erro ao fazer logout:', error);
        alert('Erro ao conectar com o servidor. Tente novamente.');
        
        // Restaura o botão em caso de erro
        logoutBtn.disabled = false;
        logoutBtn.innerHTML = originalHtml;
    }
}