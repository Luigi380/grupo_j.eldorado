// Script para gerenciar o login do admin
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Previne o comportamento padrão do formulário
            
            // Captura os valores dos campos
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Validação básica no frontend
            if (!email || !password) {
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            // Desabilita o botão durante o envio
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Entrando...';
            
            try {
                // Prepara os dados para envio
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Faz a requisição para o backend
                const response = await fetch('/grupo_j.eldorado/public/admin/login', {
                    method: 'POST',
                    body: formData
                });
                
                // Processa a resposta JSON
                const data = await response.json();
                
                // Verifica se houve erro
                if (data.error) {
                    alert(data.message || 'Erro ao fazer login');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                // Login bem-sucedido
                console.log('Login realizado com sucesso!', data);
                
                // Redireciona para o dashboard
                window.location.href = '/grupo_j.eldorado/public/admin/dashboard';
                
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Erro ao conectar com o servidor. Tente novamente.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});