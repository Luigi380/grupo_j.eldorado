const BASE_URL = "/grupo_j.eldorado/public";
let allMaterials = [];
let currentFilter = "all";
let selectedFile = null;
let editSelectedFile = null;

// Load materials on page load
document.addEventListener("DOMContentLoaded", () => {
  loadMaterials();
  setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
  // Filter buttons
  document.querySelectorAll(".filter-tabs .btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      document
        .querySelectorAll(".filter-tabs .btn")
        .forEach((b) => b.classList.remove("active"));
      e.target.classList.add("active");
      currentFilter = e.target.dataset.filter;
      renderMaterials();
    });
  });

  // Upload area - Add
  const uploadArea = document.getElementById("uploadArea");
  const fileInput = document.getElementById("materialFoto");

  uploadArea.addEventListener("click", () => fileInput.click());
  uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadArea.classList.add("dragover");
  });
  uploadArea.addEventListener("dragleave", () => {
    uploadArea.classList.remove("dragover");
  });
  uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadArea.classList.remove("dragover");
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith("image/")) {
      fileInput.files = e.dataTransfer.files;
      handleFileSelect(file);
    }
  });

  fileInput.addEventListener("change", (e) => {
    if (e.target.files.length > 0) {
      handleFileSelect(e.target.files[0]);
    }
  });

  document.getElementById("removeImage").addEventListener("click", () => {
    selectedFile = null;
    fileInput.value = "";
    document.getElementById("imagePreview").classList.add("d-none");
  });

  // Upload area - Edit
  const editUploadArea = document.getElementById("editUploadArea");
  const editFileInput = document.getElementById("editMaterialFoto");

  editUploadArea.addEventListener("click", () => editFileInput.click());
  editFileInput.addEventListener("change", (e) => {
    if (e.target.files.length > 0) {
      handleEditFileSelect(e.target.files[0]);
    }
  });

  // Save button
  document
    .getElementById("saveMaterialBtn")
    .addEventListener("click", saveMaterial);

  // Update button
  document
    .getElementById("updateMaterialBtn")
    .addEventListener("click", updateMaterial);

  // Reset form on modal close
  document
    .getElementById("addMaterialModal")
    .addEventListener("hidden.bs.modal", resetAddForm);
}

// Handle file selection for add
function handleFileSelect(file) {
  if (file.size > 5 * 1024 * 1024) {
    showError("addErrorAlert", "A imagem não pode ser maior que 5MB");
    return;
  }

  selectedFile = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    document.querySelector("#imagePreview img").src = e.target.result;
    document.getElementById("imagePreview").classList.remove("d-none");
  };
  reader.readAsDataURL(file);
}

// Handle file selection for edit
function handleEditFileSelect(file) {
  if (file.size > 5 * 1024 * 1024) {
    showError("editErrorAlert", "A imagem não pode ser maior que 5MB");
    return;
  }

  editSelectedFile = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    document.querySelector("#editImagePreview img").src = e.target.result;
  };
  reader.readAsDataURL(file);
}

// Load materials from API
async function loadMaterials() {
  try {
    const response = await fetch(`${BASE_URL}/admin/item/listar`);
    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    allMaterials = data.data || [];
    renderMaterials();
  } catch (error) {
    console.error("Erro ao carregar materiais:", error);
    showEmptyState();
  }
}

// Render materials grid
function renderMaterials() {
  const grid = document.getElementById("materialsGrid");
  const loadingState = document.getElementById("loadingState");
  const emptyState = document.getElementById("emptyState");

  loadingState.classList.add("d-none");

  let filteredMaterials = allMaterials;
  if (currentFilter !== "all") {
    filteredMaterials = allMaterials.filter((m) => m.tipo === currentFilter);
  }

  if (filteredMaterials.length === 0) {
    grid.classList.add("d-none");
    emptyState.classList.remove("d-none");
    return;
  }

  emptyState.classList.add("d-none");
  grid.classList.remove("d-none");

  grid.innerHTML = filteredMaterials
    .map(
      (material) => `
          <div class="col-md-6 col-lg-4">
            <div class="card material-card">
              <img src="${material.foto}" class="material-image" alt="${
        material.nome
      }">
              <div class="card-body">
                <span class="badge bg-primary mb-2">${
                  material.tipo || "Sem tipo"
                }</span>
                <h5 class="card-title">${material.nome}</h5>
                <p class="card-text text-muted">${material.texto.substring(
                  0,
                  100
                )}...</p>
                <small class="text-muted">
                  <i class="fa-solid fa-user me-1"></i>
                  Cadastrado por: ${material.admin_email || "N/A"}
                </small>
              </div>
              <div class="card-footer bg-transparent d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary btn-action flex-fill" onclick="editMaterial('${
                  material.id_itens
                }')">
                  <i class="fa-solid fa-edit me-1"></i>Editar
                </button>
                <button class="btn btn-sm btn-outline-danger btn-action flex-fill" onclick="deleteMaterial('${
                  material.id_itens
                }', '${material.nome}')">
                  <i class="fa-solid fa-trash me-1"></i>Excluir
                </button>
              </div>
            </div>
          </div>
        `
    )
    .join("");
}

// Save new material
async function saveMaterial() {
  const tipo = document.getElementById("materialTipo").value;
  const nome = document.getElementById("materialNome").value;
  const texto = document.getElementById("materialTexto").value;

  if (!tipo || !nome || !texto || !selectedFile) {
    showError(
      "addErrorAlert",
      "Preencha todos os campos e selecione uma imagem"
    );
    return;
  }

  const saveBtn = document.getElementById("saveMaterialBtn");
  saveBtn.disabled = true;
  saveBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

  try {
    // Upload image
    const imgUrl = await uploadImage(selectedFile, "uploadProgress");

    // Save material
    const formData = new FormData();
    formData.append("tipo", tipo);
    formData.append("name", nome);
    formData.append("text", texto);
    formData.append("img_url", imgUrl);

    const response = await fetch(`${BASE_URL}/admin/item/cadastrar`, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    // Close modal and reload
    bootstrap.Modal.getInstance(
      document.getElementById("addMaterialModal")
    ).hide();
    loadMaterials();
    resetAddForm();
  } catch (error) {
    showError("addErrorAlert", error.message);
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i class="fa-solid fa-save me-2"></i>Salvar Material';
  }
}

// Edit material
async function editMaterial(id) {
  try {
    const response = await fetch(`${BASE_URL}/admin/item/exibir/${id}`);
    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    const material = data.data;

    // Fill form
    document.getElementById("editMaterialId").value = material.id_itens;
    document.getElementById("editMaterialTipo").value = material.tipo || "";
    document.getElementById("editMaterialNome").value = material.nome;
    document.getElementById("editMaterialTexto").value = material.texto;
    document.getElementById("editMaterialFotoAtual").value = material.foto;
    document.querySelector("#editImagePreview img").src = material.foto;

    // Reset edit file
    editSelectedFile = null;
    document.getElementById("editMaterialFoto").value = "";

    // Show modal
    new bootstrap.Modal(document.getElementById("editMaterialModal")).show();
  } catch (error) {
    alert("Erro ao carregar material: " + error.message);
  }
}

// Update material
async function updateMaterial() {
  const id = document.getElementById("editMaterialId").value;
  const tipo = document.getElementById("editMaterialTipo").value;
  const nome = document.getElementById("editMaterialNome").value;
  const texto = document.getElementById("editMaterialTexto").value;
  let fotoUrl = document.getElementById("editMaterialFotoAtual").value;

  if (!tipo || !nome || !texto) {
    showError("editErrorAlert", "Preencha todos os campos");
    return;
  }

  const updateBtn = document.getElementById("updateMaterialBtn");
  updateBtn.disabled = true;
  updateBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';

  try {
    // Upload new image if selected
    if (editSelectedFile) {
      fotoUrl = await uploadImage(editSelectedFile, "editUploadProgress");
    }

    const formData = new FormData();
    formData.append("tipo", tipo);
    formData.append("name", nome);
    formData.append("text", texto);
    formData.append("img_url", fotoUrl);

    const response = await fetch(`${BASE_URL}/admin/item/atualizar/${id}`, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    bootstrap.Modal.getInstance(
      document.getElementById("editMaterialModal")
    ).hide();
    loadMaterials();
  } catch (error) {
    showError("editErrorAlert", error.message);
  } finally {
    updateBtn.disabled = false;
    updateBtn.innerHTML =
      '<i class="fa-solid fa-save me-2"></i>Atualizar Material';
  }
}

// Delete material
async function deleteMaterial(id, nome) {
  if (!confirm(`Tem certeza que deseja excluir "${nome}"?`)) {
    return;
  }

  try {
    const response = await fetch(`${BASE_URL}/admin/item/deletar/${id}`, {
      method: "DELETE",
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    loadMaterials();
  } catch (error) {
    alert("Erro ao excluir material: " + error.message);
  }
}

// Upload image to Supabase Storage
async function uploadImage(file, progressBarId) {
  const progressBar = document.getElementById(progressBarId);
  progressBar.classList.remove("d-none");

  const formData = new FormData();
  formData.append("file", file);
  formData.append("bucket", "imagens_itens");

  // Simulate progress (real progress would need backend support)
  let progress = 0;
  const progressInterval = setInterval(() => {
    progress += 10;
    progressBar.querySelector(".progress-bar").style.width = progress + "%";
    if (progress >= 90) clearInterval(progressInterval);
  }, 100);

  try {
    const response = await fetch(`${BASE_URL}/admin/upload-imagem`, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.message);
    }

    clearInterval(progressInterval);
    progressBar.querySelector(".progress-bar").style.width = "100%";
    setTimeout(() => progressBar.classList.add("d-none"), 500);

    return data.url;
  } catch (error) {
    clearInterval(progressInterval);
    progressBar.classList.add("d-none");
    throw error;
  }
}

// Helper functions
function showError(elementId, message) {
  const alert = document.getElementById(elementId);
  alert.textContent = message;
  alert.classList.remove("d-none");
  setTimeout(() => alert.classList.add("d-none"), 5000);
}

function showEmptyState() {
  document.getElementById("loadingState").classList.add("d-none");
  document.getElementById("emptyState").classList.remove("d-none");
  document.getElementById("materialsGrid").classList.add("d-none");
}

function resetAddForm() {
  document.getElementById("addMaterialForm").reset();
  document.getElementById("imagePreview").classList.add("d-none");
  document.getElementById("addErrorAlert").classList.add("d-none");
  selectedFile = null;
}
