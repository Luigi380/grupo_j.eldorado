document.addEventListener('DOMContentLoaded', () => {
  loadAdminInfo();
  setupPasswordToggles();
  setupForms();
});

// Carrega informações do admin atual
async function loadAdminInfo() {
  try {
    const response = await fetch('/grupo_j.eldorado/public/admin/current', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (!data.error) {
      document.getElementById('currentEmail').value = data.email;
    } else {
      showToast('Erro ao carregar informações: ' + data.message, 'danger');
    }
  } catch (error) {
    console.error('Erro ao carregar informações:', error);
    showToast('Erro ao carregar informações do administrador', 'danger');
  }
}

// Configura os botões de toggle de senha
function setupPasswordToggles() {
  const toggleButtons = [
    { btn: 'toggleEmailPassword', input: 'emailPassword' },
    { btn: 'toggleCurrentPassword', input: 'currentPassword' },
    { btn: 'toggleNewPassword', input: 'newPassword' },
    { btn: 'toggleConfirmPassword', input: 'confirmPassword' }
  ];

  toggleButtons.forEach(({ btn, input }) => {
    const button = document.getElementById(btn);
    const inputField = document.getElementById(input);

    if (button && inputField) {
      button.addEventListener('click', () => {
        const type = inputField.type === 'password' ? 'text' : 'password';
        inputField.type = type;
        
        const icon = button.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      });
    }
  });
}

// Configura os formulários
function setupForms() {
  const emailForm = document.getElementById('updateEmailForm');
  if (emailForm) {
    emailForm.addEventListener('submit', handleEmailUpdate);
  }

  const passwordForm = document.getElementById('updatePasswordForm');
  if (passwordForm) {
    passwordForm.addEventListener('submit', handlePasswordUpdate);
  }
}

// Atualiza o email
async function handleEmailUpdate(e) {
  e.preventDefault();

  const newEmail = document.getElementById('newEmail').value;
  const password = document.getElementById('emailPassword').value;
  const currentEmail = document.getElementById('currentEmail').value;

  // Validações
  if (newEmail === currentEmail) {
    showToast('O novo email é igual ao email atual', 'warning');
    return;
  }

  if (!validateEmail(newEmail)) {
    showToast('Email inválido', 'danger');
    return;
  }

  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Atualizando...';

  try {
    const formData = new FormData();
    formData.append('newEmail', newEmail);
    formData.append('password', password);

    const response = await fetch('/grupo_j.eldorado/public/admin/update-email', {
      method: 'PUT',
      body: formData
    });

    const data = await response.json();

    if (data.error) {
      showToast(data.message, 'danger');
    } else {
      showToast('Email atualizado com sucesso! Você será redirecionado para o login.', 'success');
      
      // Limpa o formulário
      e.target.reset();
      document.getElementById('currentEmail').value = newEmail;
      
      // Aguarda 2 segundos e redireciona para login
      setTimeout(() => {
        window.location.href = '/grupo_j.eldorado/public/admin/login';
      }, 2000);
    }
  } catch (error) {
    console.error('Erro:', error);
    showToast('Erro ao atualizar email', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

// Atualiza a senha
async function handlePasswordUpdate(e) {
  e.preventDefault();

  const currentPassword = document.getElementById('currentPassword').value;
  const newPassword = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

  // Validações
  if (newPassword.length < 6) {
    showToast('A nova senha deve ter no mínimo 6 caracteres', 'warning');
    return;
  }

  if (newPassword !== confirmPassword) {
    showToast('As senhas não coincidem', 'danger');
    return;
  }

  if (currentPassword === newPassword) {
    showToast('A nova senha deve ser diferente da atual', 'warning');
    return;
  }

  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Atualizando...';

  try {
    const formData = new FormData();
    formData.append('currentPassword', currentPassword);
    formData.append('newPassword', newPassword);

    // CORREÇÃO: Usar PUT ao invés de POST
    const response = await fetch('/grupo_j.eldorado/public/admin/update-password', {
      method: 'PUT',
      body: formData
    });

    const data = await response.json();

    if (data.error) {
      showToast(data.message, 'danger');
    } else {
      showToast('Senha atualizada com sucesso!', 'success');
      
      // Limpa o formulário
      e.target.reset();
    }
  } catch (error) {
    console.error('Erro:', error);
    showToast('Erro ao atualizar senha', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

// Valida email
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Mostra toast de notificação
function showToast(message, type = 'info') {
  // Remove toasts anteriores
  const existingToast = document.querySelector('.custom-toast');
  if (existingToast) {
    existingToast.remove();
  }

  const toast = document.createElement('div');
  toast.className = `custom-toast alert alert-${type} alert-dismissible fade show`;
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  `;
  
  toast.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

  document.body.appendChild(toast);

  // Remove automaticamente após 5 segundos
  setTimeout(() => {
    toast.remove();
  }, 5000);
}