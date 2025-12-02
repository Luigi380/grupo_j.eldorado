/**
 * Script para carregar dinamicamente os mármores na página pública
 * Arquivo: /grupo_j.eldorado/public/js/home/load-marmores.js
 */

const BASE_URL = "/grupo_j.eldorado/public";

// Carrega os mármores quando a página estiver pronta
document.addEventListener("DOMContentLoaded", loadMarmores);

async function loadMarmores() {
  const container = document.querySelector("main.content-list");

  if (!container) {
    console.error("Container de mármores não encontrado");
    return;
  }

  // Mostrar loading
  showLoading(container);

  try {
    const response = await fetch(
      `${BASE_URL}/api/materiais/publico/listar?tipo=Mármore`
    );
    const data = await response.json();

    if (data.error) {
      showError(container, data.message);
      return;
    }

    const marmores = data.data || [];

    if (marmores.length === 0) {
      showEmpty(container);
      return;
    }

    renderMarmores(container, marmores);
  } catch (error) {
    console.error("Erro ao carregar mármores:", error);
    showError(
      container,
      "Erro ao carregar os materiais. Tente novamente mais tarde."
    );
  }
}

function renderMarmores(container, marmores) {
  container.innerHTML = marmores
    .map((marmore, index) => {
      const isEven = index % 2 === 0;

      return `
            <article class="row align-items-center mb-5 marble-item" data-aos="fade-up" data-aos-delay="${
              index * 100
            }">
                <div class="col-md-7 ${isEven ? "" : "order-md-1"}">
                    <h3 class="marble-name">${escapeHtml(marmore.nome)}</h3>
                    <p class="marble-desc">${escapeHtml(marmore.texto)}</p>
                </div>
                <div class="col-md-5 d-flex justify-content-end">
                    <div class="marble-image shadow">
                        <img
                            src="${marmore.foto}"
                            alt="${escapeHtml(marmore.nome)}"
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
            <p class="mt-3 text-muted">Carregando mármores...</p>
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
            <h5 class="text-muted">Nenhum mármore cadastrado</h5>
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
