// Script para gerenciar cadastro de admin
document.addEventListener('DOMContentLoaded', function(){
    const registerForm = document.getElementById('registerForm');

    if(registerForm){
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Captura os valores dos campos
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const passwordConfirm = document.getElementById('passwordConfirm').value.trim();
            const adminPassword = document.getElementById('adminPassword').value.trim();

            if (!email || !password || !passwordConfirm || !adminPassword){
                alert('Por favor, preencha todos os campos!');
                return;
            }

            if (password !== passwordConfirm){
                alert('Os campos de Senha e Confirmação de Senha não coincidem!');
                return;
            }

            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Cadastrando...';

            try {
                // Prepara os dados para envio
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                formData.append('passwordConfirm', passwordConfirm);
                formData.append('adminPassword', adminPassword); // Senha do admin atual
                
                // Faz a requisição para o backend
                const response = await fetch('/grupo_j.eldorado/public/admin/cadastrar', {
                    method: 'POST',
                    body: formData
                });
                
                // Processa a resposta JSON
                const data = await response.json();
                
                // Verifica se houve erro
                if (data.error) {
                    alert(data.message || 'Erro ao fazer o cadastro');
                    submitBtn.disabled = false; 
                    submitBtn.textContent = originalText;
                    return;
                }
                
                // Cadastro bem-sucedido
                console.log('Cadastro realizado com sucesso!', data);
                alert('Administrador cadastrado com sucesso!');
                
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