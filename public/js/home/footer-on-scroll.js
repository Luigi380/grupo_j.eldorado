(function(){
  const footer = document.querySelector('.site-footer');
  if(!footer) return;

  let lastY = window.pageYOffset || document.documentElement.scrollTop;
  let ticking = false;

  function updateVisibility(){
    const currentY = window.pageYOffset || document.documentElement.scrollTop;
    const scrollingDown = currentY > lastY;

    // Em telas pequenas não ativamos o footer flutuante
    if(window.innerWidth <= 600){
      footer.classList.remove('visible');
      footer.classList.remove('floating');
      lastY = currentY <= 0 ? 0 : currentY;
      return;
    }

    if(scrollingDown && currentY > 150){
      // ativa modo flutuante e mostra
      footer.classList.add('floating');
      // delay pequeno para garantir que a classe floating foi aplicada antes de mostrar
      requestAnimationFrame(()=> footer.classList.add('visible'));
    } else {
      // esconde
      footer.classList.remove('visible');
      // remove floating após a transição para voltar ao fluxo
      setTimeout(()=> footer.classList.remove('floating'), 300);
    }

    lastY = currentY <= 0 ? 0 : currentY;
  }

  window.addEventListener('scroll', function(){
    if(!ticking){
      window.requestAnimationFrame(function(){
        updateVisibility();
        ticking = false;
      });
      ticking = true;
    }
  }, {passive: true});

  // inicializa conforme posição atual
  updateVisibility();
})();