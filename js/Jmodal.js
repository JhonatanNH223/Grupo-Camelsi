const btnOpenInsertar = document.querySelector("#abrirModAgregar");
const btnCloseInsertar = document.querySelector("#btn-cerrar-incertar");

const btnOpenEditar = document.querySelector("#abrirModEditar");
const btnCloseEditar = document.querySelector("#btn-cerrar-editar");

const modal1 = document.querySelector('.modal1');
const modal2 = document.querySelector('.modal2');

btnOpenInsertar.addEventListener('click', (e)=>{
    e.preventDefault();
    modal1.classList.add('modal1--show');
})

btnCloseInsertar.addEventListener('click', (e)=>{
    e.preventDefault();
    modal1.classList.remove('modal1--show');
})

btnOpenEditar.addEventListener('click', (e)=>{
    e.preventDefault();
    modal2.classList.add('modal2--show');
})

btnCloseEditar.addEventListener('click', (e)=>{
    e.preventDefault();
    modal2.classList.remove('modal2--show');
})



