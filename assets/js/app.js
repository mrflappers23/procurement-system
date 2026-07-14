function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }

document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.overlay').forEach(function(o){
    o.addEventListener('click', function(e){ if(e.target === o) o.classList.remove('open'); });
  });
});

function confirmAction(message){
  return window.confirm(message);
}
