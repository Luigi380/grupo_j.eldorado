/**
 * Script para carregar dinamicamente os granitos na página pública
 * Arquivo: /grupo_j.eldorado/public/js/home/load-granitos.js
 */

const BASE_URL = "/grupo_j.eldorado/public";

// Carrega os granitos quando a página estiver pronta
document.addEventListener("DOMContentLoaded", loadGranitos);

async function loadGranitos() {
  const container = document.querySelector("main.content-list");

  if (!container) {
    console.error("Container de granitos não encontrado");
    return;
  }

  // Mostrar loading
  showLoading(container);

  try {
    const response = await fetch(
      `${BASE_URL}/api/materiais/publico/listar?tipo=Granito`
    );
    const data = await response.json();

    if (data.error) {
      showError(container, data.message);
      return;
    }

    const granitos = data.data || [];

    if (granitos.length === 0) {
      showEmpty(container);
      return;
    }

    renderGranitos(container, granitos);
  } catch (error) {
    console.error("Erro ao carregar granitos:", error);
    showError(
      container,
      "Erro ao carregar os materiais. Tente novamente mais tarde."
    );
  }
}

function renderGranitos(container, granitos) {
  container.innerHTML = granitos
    .map((granito, index) => {
      const isEven = index % 2 === 0;

      return `
            <article class="row align-items-center mb-5 marble-item" data-aos="fade-up" data-aos-delay="${
              index * 100
            }">
                <div class="col-md-7 ${isEven ? "" : "order-md-1"}">
                    <h3 class="marble-name">${escapeHtml(granito.nome)}</h3>
                    <p class="marble-desc">${escapeHtml(granito.texto)}</p>
                </div>
                <div class="col-md-5 d-flex justify-content-end">
                    <div class="marble-image shadow">
                        <img
                            src="${granito.foto}"
                            alt="${escapeHtml(granito.nome)}"
                            onerror="this.src='/grupo_j.eldorado/public/img/placeholder.png'"
                        />
                    </div>
                </div>
            </article>
        `;
    })
    .join("");
}

function showLoading(container) {
  container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">Carregando granitos...</p>
        </div>
    `;
}

function showError(container, message) {
  container.innerHTML = `
        <div class="alert alert-danger text-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            ${escapeHtml(message)}
        </div>
    `;
}

function showEmpty(container) {
  container.innerHTML = `
        <div class="text-center py-5">
            <i class="fa-solid fa-box-open fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Nenhum granito cadastrado</h5>
            <p class="text-muted">Em breve teremos novos materiais disponíveis.</p>
        </div>
    `;
}

// Função para escapar HTML e prevenir XSS
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
