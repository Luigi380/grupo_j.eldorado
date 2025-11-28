document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (!loginForm) {
        console.error('Formulário de login não encontrado!');
        return;
    }

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault(); // Previne o comportamento padrão do formulário
        
        // Pega os valores dos campos
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const rememberMe = document.getElementById('remember').checked;
        
        // Validação básica no front
        if (!email || !password) {
            showMessage('Por favor, preencha todos os campos!', 'error');
            return;
        }

        // Validação de email
        if (!isValidEmail(email)) {
            showMessage('Por favor, insira um e-mail válido!', 'error');
            return;
        }

        // Desabilita o botão durante a requisição
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Carregando...';

        try {
            // Cria o FormData para enviar
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            // Faz a requisição para o backend
            const response = await fetch('/auth/login', {
                method: 'POST',
                body: formData
            });

            // Verifica se a resposta é JSON válida
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta do servidor não é JSON válida');
            }

            const result = await response.json();

            if (result.error) {
                showMessage(result.message || 'Erro ao fazer login', 'error');
            } else {
                showMessage(result.message || 'Login realizado com sucesso!', 'success');
                
                // Se "lembrar-me" estiver marcado, salva no localStorage
                if (rememberMe) {
                    localStorage.setItem('remember_email', email);
                } else {
                    localStorage.removeItem('remember_email');
                }

                // Redireciona após 1 segundo
                setTimeout(() => {
                    window.location.href = '/admin/dashboard';
                }, 1000);
            }

        } catch (error) {
            console.error('Erro na requisição:', error);
            showMessage('Erro ao conectar com o servidor. Tente novamente.', 'error');
        } finally {
            // Reabilita o botão
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });

    // Preenche o email se "lembrar-me" foi marcado anteriormente
    const rememberedEmail = localStorage.getItem('remember_email');
    if (rememberedEmail) {
        document.getElementById('email').value = rememberedEmail;
        document.getElementById('remember').checked = true;
    }
});

// Função para validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Função para mostrar mensagens ao usuário
function showMessage(message, type) {
    // Remove mensagem anterior se existir
    const existingAlert = document.querySelector('.alert-message');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Cria o elemento de alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show alert-message`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Adiciona ao body
    document.body.appendChild(alertDiv);

    // Remove automaticamente após 5 segundos
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
}