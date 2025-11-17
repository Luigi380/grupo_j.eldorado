<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Últimos Trabalhos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/home/ultimosTrabalhos.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="section_last_work">
  <div class="container">
    
    <h1 class="section-title">Últimos trabalhos</h1>

    <!-- Pia -->
    <div class="last_work_container d-flex flex-wrap align-items-center gap-4 my-4">
      <div class="work-content" style="flex:1 1 300px;">
        <h2>Pia</h2>
        <p>Somos uma empresa especializada em mármores e granitos, comprometida em oferecer produtos de alta qualidade e excelente acabamento. Valorizamos a satisfação do cliente, a inovação e o respeito ao meio ambiente em todos os nossos processos.</p>
        <button class="btn btn-warning btn-more">Ver mais</button>
      </div>
      <div class="work-image" style="flex:0 0 320px;">
        <img src="https://via.placeholder.com/300x200?text=Pia+em+Granito" alt="Pia em Granito" class="img-fluid rounded">
      </div>
    </div>

    <!-- Bancada -->
    <div class="last_work_container d-flex flex-wrap align-items-center gap-4 my-4">
      <div class="work-content" style="flex:1 1 300px;">
        <h2>Bancada</h2>
        <p>Nossa bancada é feita com materiais nobres e acabamento impecável. Ideal para cozinhas modernas, combinando beleza e funcionalidade. Projetos personalizados para cada espaço.</p>
        <button class="btn btn-warning btn-more">Ver mais</button>
      </div>
      <div class="work-image" style="flex:0 0 320px;">
        <img src="https://via.placeholder.com/300x200?text=Bancada+Moderna" alt="Bancada Moderna" class="img-fluid rounded">
      </div>
    </div>

    <!-- Escada -->
    <div class="last_work_container d-flex flex-wrap align-items-center gap-4 my-4">
      <div class="work-content" style="flex:1 1 300px;">
        <h2>Escada</h2>
        <p>Projetos elegantes e robustos para escadas internas e externas. Utilizamos pedras naturais resistentes e duráveis, garantindo segurança e sofisticação ao seu imóvel.</p>
        <button class="btn btn-warning btn-more">Ver mais</button>
      </div>
      <div class="work-image" style="flex:0 0 320px;">
        <img src="https://via.placeholder.com/300x200?text=Escada+em+Mármore" alt="Escada em Mármore" class="img-fluid rounded">
      </div>
    </div>

    <!-- Featured: Churrasqueira -->
    <div class="featured-work mb-4">
      <div class="featured-content">
        <h2>Churrasqueira</h2>
        <p>Somos uma empresa especializada em mármores e granitos, comprometida em oferecer produtos de alta qualidade e excelente acabamento. Valorizamos a satisfação do cliente, a inovação e o respeito ao meio ambiente em todos os nossos processos.</p>
        <button class="btn btn-more btn-warning">Ver mais</button>
      </div>
      <div class="featured-image">
        <img src="https://via.placeholder.com/420x260?text=Churrasqueira" alt="Churrasqueira" class="img-fluid">
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
