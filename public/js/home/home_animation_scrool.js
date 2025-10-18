// fallback JS para garantir scroll suave em navegadores que não suportam CSS
      (function(){
        // captura todos os links internos que começam com #
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor){
          anchor.addEventListener('click', function(e){
            var targetId = this.getAttribute('href').substring(1);
            var targetEl = document.getElementById(targetId);
            if(targetEl){
              e.preventDefault();
              targetEl.scrollIntoView({behavior: 'smooth', block: 'start'});
              // atualiza a hash no endereço sem pular instantaneamente
              history.pushState(null, '', '#'+targetId);
            }
          });
        });
      })();