const stars = document.querySelectorAll('.star');
const avaliacaoTexto = document.getElementById('avaliacao-texto');
let selectedValue = 0;

// Adiciona os eventos nas estrelas
stars.forEach(star => {
  star.addEventListener('mouseover', () => highlightStars(star));
  star.addEventListener('mouseout', () => highlightStarsOnSelect());
  star.addEventListener('click', () => selectStar(star));
});

// Função que altera a cor das estrelas ao passar o mouse
function highlightStars(star) {
  const value = parseInt(star.getAttribute('data-value'));
  stars.forEach((s, index) => {
    s.style.color = index < value ? '#f5a623' : '#ccc';
  });
}

// Função que mantém as estrelas coloridas após a seleção
function highlightStarsOnSelect() {
  stars.forEach((star, index) => {
    star.style.color = index < selectedValue ? '#f5a623' : '#ccc';
  });
}

// Função que define o valor selecionado ao clicar na estrela
function selectStar(star) {
  selectedValue = parseInt(star.getAttribute('data-value'));
  stars.forEach((s, index) => {
    s.classList.toggle('selected', index < selectedValue);
  });
  updateAvaliacaoTexto(selectedValue);
}

// Atualiza o texto da avaliação
function updateAvaliacaoTexto(valor) {
  const textos = [
    '', 
    'Não Satisfeito', 
    'Pouco Satisfeito', 
    'Razoavelmente Satisfeito', 
    'Satisfeito', 
    'Muito Satisfeito'
  ];
  avaliacaoTexto.textContent = textos[valor] || '';
}


// Função que verifica no servidor se o ticket já foi avaliado
function verificarAvaliacao(ticketId) {
  fetch(`php/verificar_avaliacao.php?ticket_id=${ticketId}`)
    .then(res => res.json())
    .then(data => {
      if (data.ja_avaliado) {
        // redireciona imediatamente para a página “já avaliado”
        window.location.href = "html/ja_avaliado.html";
      }
    })
    .catch(err => console.error('Erro ao verificar avaliação:', err));
}

function getTicketInfo() {
  const params = new URLSearchParams(window.location.search);
  const id    = params.get('ticket_id');
  const name  = params.get('ticket_name');
  const date  = params.get('ticket_createdate');

  document.getElementById('ticket-id').textContent   = id || 'N/A';
  document.getElementById('ticket-name').textContent = name ? decodeURIComponent(name) : 'N/A';
  document.getElementById('ticket-date').textContent = date || 'N/A';

  if (id) {
    verificarAvaliacao(id);
    
    // Chama o PHP para buscar o nome do requerente e a data de abertura
    fetch(`php/buscarDados.php?ticket_id=${id}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('ticket-requester').textContent = data.requester_name || 'N/A';
        // Preenche a data de abertura com o valor retornado do banco
        document.getElementById('ticket-date').textContent = data.ticket_createdate ? new Date(data.ticket_createdate).toLocaleString() : 'N/A';
      })
      .catch(err => {
        console.error('Erro ao buscar dados do ticket:', err);
        document.getElementById('ticket-requester').textContent = 'Erro';
        document.getElementById('ticket-date').textContent = 'Erro';
      });
  }
}

getTicketInfo();

// Agora o fetch dentro do listener de clique
document.getElementById('enviar').addEventListener('click', () => {
  const comentario       = document.getElementById('feedback').value;
  const ticket_id        = document.getElementById('ticket-id').textContent;
  const ticket_name      = document.getElementById('ticket-name').textContent;
  const ticket_createdate = document.getElementById('ticket-date').textContent;

  if (selectedValue === 0) {
    alert("Por favor, selecione uma avaliação.");
    return;
  }

    const requester_name = document.getElementById('ticket-requester').textContent;

    fetch('php/salvar.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ticket_id,
        ticket_name,
        ticket_createdate,
        requester_name,
        estrelas: selectedValue,
        comentario
      })
    })
    

  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = "html/agradecimento.html";
    } else {
      alert(data.error || "Erro ao enviar avaliação.");
    }
  })
  .catch(err => {
    console.error('Erro na requisição:', err);
    alert("Erro inesperado ao enviar avaliação.");
  });
});

function buscarRequerente(ticketId) {
  fetch(`php/buscarDados.php?ticket_id=${ticketId}`)
    .then(res => res.json())
    .then(data => {
      if (data.requester_name) {
        const p = document.createElement('p');
        p.className = "infoCham";
        p.innerHTML = `<strong>REQUERENTE:</strong> ${data.requester_name}`;
        document.querySelector('.ticket-info').appendChild(p);
      }
    })
    .catch(err => console.error('Erro ao buscar nome do requerente:', err));
}
