
/**
 * Gerenciamento de Administradores
 * Sistema para listar e excluir administradores
 */

// Função para carregar os administradores
      async function loadAdmins() {
        console.log('🔄 Carregando administradores...');
        
        const loadingMessage = document.getElementById('loadingMessage');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const tableContainer = document.getElementById('tableContainer');
        const emptyMessage = document.getElementById('emptyMessage');
        const adminsTableBody = document.getElementById('adminsTableBody');
        const adminsCount = document.getElementById('adminsCount');

        // Mostrar loading
        loadingMessage.classList.remove('d-none');
        errorMessage.classList.add('d-none');
        tableContainer.classList.add('d-none');
        emptyMessage.classList.add('d-none');

        try {
          const response = await fetch('/grupo_j.eldorado/public/admin/listar-admins', {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json'
            }
          });

          if (!response.ok) {
            throw new Error('Erro ao carregar administradores');
          }

          const admins = await response.json();

          // Esconder loading
          loadingMessage.classList.add('d-none');

          // Verificar se há admins
          if (!admins || admins.length === 0) {
            emptyMessage.classList.remove('d-none');
            adminsCount.textContent = '0';
            return;
          }

          // Limpar tabela
          adminsTableBody.innerHTML = '';

          // Preencher tabela
          admins.forEach((admin, index) => {
            const row = document.createElement('tr');
            
            // IMPORTANTE: Armazenar o ID como string e escapar para HTML
            const adminId = escapeHtml(String(admin.id_adm));
            const adminEmail = escapeHtml(String(admin.email));
            
            row.innerHTML = `
              <th scope="row" class="text-center text-muted">${index + 1}</th>
              <td>
                <div class="d-flex align-items-center">
                  <div class="admin-avatar me-3">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div>
                    <div class="fw-semibold">${adminEmail}</div>
                    <small class="text-muted">ID: ${adminId}</small>
                  </div>
                </div>
              </td>
              <td class="text-center table-actions">
                <button 
                  class="btn btn-sm btn-delete-custom" 
                  onclick='deleteAdmin("${adminId}", "${adminEmail}")'
                  title="Excluir administrador"
                >
                  <i class="fa-solid fa-trash-can me-1"></i> Excluir
                </button>
              </td>
            `;
            adminsTableBody.appendChild(row);
          });

          // Mostrar tabela
          tableContainer.classList.remove('d-none');

        } catch (error) {
          console.error('Erro ao carregar:', error);
          loadingMessage.classList.add('d-none');
          errorText.textContent = 'Erro ao carregar administradores. Tente novamente.';
          errorMessage.classList.remove('d-none');
        }
      }

      // Função para excluir um administrador
      async function deleteAdmin(id, email) {
        console.log('Tentando excluir admin:', { id, email });
        
        // Confirmação com alert simples
        const confirmDelete = confirm(
          `⚠️ ATENÇÃO!\n\nDeseja realmente excluir o administrador?\n\nEmail: ${email}\nID: ${id}\n\nEsta ação não pode ser desfeita.`
        );

        if (!confirmDelete) {
          return;
        }

        try {
          const response = await fetch(`/grupo_j.eldorado/public/admin/deletar/${id}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json'
            }
          });

          const result = await response.json();
          console.log('Dados da resposta:', result);

          if (!response.ok || result.error) {
            throw new Error(result.message || 'Erro ao excluir administrador');
          }

          // Mostrar mensagem de sucesso
          alert('✅ Administrador excluído com sucesso!');

          // Recarregar a lista
          loadAdmins();

        } catch (error) {
          console.error('Erro ao excluir:', error);
          alert('❌ Erro ao excluir administrador:\n\n' + error.message);
        }
      }

      // Função auxiliar para escapar HTML e prevenir XSS
      function escapeHtml(text) {
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
      }

      // Carregar admins quando a página carregar
      document.addEventListener('DOMContentLoaded', function() {
        console.log('📄 Página carregada, iniciando dashboard...');
        loadAdmins();
      });