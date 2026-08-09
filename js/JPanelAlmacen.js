
/* Clase de funcionamiento recorrido */
class Principal {
    /* Contructor de l aclase */
    constructor() {
        this.apiUrlAlmacen = 'almacen/api-almacen.php';
        this.apiUrlTipo = 'almacen/api-tipo.php';
        this.Tipos = []; //Arreglo donde se guardan todos los eleemtos
        this.Almacen = [];

        this.elements = {
            /* Tabla del recorrido */
            listTipo: document.getElementById('listTipo'),
            listAlmacen: document.getElementById('listAlmacen'),

            contadorAlmacen: document.getElementById('contadorAlmacen'),
            contadorTipos: document.getElementById('contadorTipos'),

            //Elementos de espera o rsultado
            loading: document.getElementById('loading'),
            sinResultados: document.getElementById('sinResultados'),

            loading2: document.getElementById('loading2'),
            sinResultados2: document.getElementById('sinResultados2'),
        };

        /* Llamada al metodo INIT */
        this.init();
    }

    /* Metodo para inicializar (se ejecuta al inicio) */
    init() {
        this.cargar();
    }

    async cargar() {
        try {
            // Mostrar indicador de carga y ocultar otros elementos
            this.elements.loading.classList.remove('hidden');
            this.elements.sinResultados.classList.add('hidden');

            this.elements.loading2.classList.remove('hidden');
            this.elements.sinResultados2.classList.add('hidden');

            // Realizar petición a la API usando el ID actual
            const response = await fetch(`${this.apiUrlAlmacen}`);
            const result = await response.json();

            const response2 = await fetch(`${this.apiUrlTipo}`);
            const result2 = await response2.json();

            // Ocultar el indicador de carga
            this.elements.loading.classList.add('hidden');
            this.elements.loading2.classList.add('hidden');

            // Si se encontraron datos
            if (result.success && result.data.length > 0) {
                // Mostrar el contenedor de datos y renderizar tabla
                this.renderListaAlmacen(result.data);
                this.elements.contadorAlmacen.textContent = result.data.length;
                this.contadorBajoStock(result.data);
                this.renderListaStockBajo(result.data);
                //this.elementos = result.data; // Guardar datos en la propiedad

            } else {
                // Mostrar mensaje de "sin resultados"
                this.elements.sinResultados.classList.remove('hidden');
                //this.mostrarToast(result.message || 'No se encontraron registros', 'info');
            }


            // Si se encontraron datos
            if (result2.success && result2.data.length > 0) {
                // Mostrar el contenedor de datos y renderizar tabla
                //this.elementos = result2.data; // Guardar datos en la propiedad
                this.renderListaTipos(result2.data);
                this.elements.contadorTipos.textContent = result2.data.length;

            } else {
                // Mostrar mensaje de "sin resultados"
                this.elements.sinResultados2.classList.remove('hidden');
                //this.mostrarToast(result2.message || 'No se encontraron registros', 'info');
            }
        } catch (error) {
            // En caso de error, ocultar carga, mostrar mensaje y detalles del error
            this.elements.loading.classList.add('hidden');
            this.elements.sinResultados.classList.remove('hidden');

            this.elements.loading2.classList.add('hidden');
            this.elements.sinResultados2.classList.remove('hidden');
            //this.elements.centro_dato.classList.remove('hidden');

            //this.mostrarToast('Error al cargar datos: ' + error.message, 'error');
        }
    }

    /* Metodo para Renderizar las listas  */
    renderListaTipos(data) {
        const lista = document.getElementById('listTipo');
        lista.innerHTML = '';

        // Tomar los últimos 4 elementos (o menos si hay menos)
        const ultimos = data.slice(-4).reverse(); // esto toma los últimos 4 elementos del arreglo

        ultimos.forEach(item => {
            const tipo = item.tipo.trim().toLowerCase();
            const nom = item.nombre.trim().toLowerCase();

            const li = document.createElement('li');
            li.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1) + " " + nom.charAt(0).toUpperCase() + nom.slice(1);
            lista.appendChild(li);
        });
    }

    //Renderiza la lista de los elementos más reciedntes de almacen
    renderListaAlmacen(data) {
        const lista = document.getElementById('listAlmacen');
        lista.innerHTML = '';

        // Tomar los últimos 5 elementos (o menos si hay menos)
        const ultimos = data.slice(-5).reverse(); // esto toma los últimos 5 elementos del arreglo

        ultimos.forEach(item => {
            const tipo = item.tipo.trim().toLowerCase();
            const nom = item.nombre.trim().toLowerCase();
            const stock = item.stock;

            const li = document.createElement('li');
            li.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1) + " " + nom.charAt(0).toUpperCase() + nom.slice(1) + " -> " + stock;
            lista.appendChild(li);
        });
    }

    // Rederiza la lista de los elementos falto de stock del almacen
    renderListaStockBajo(data) {
        const lista = document.getElementById('listStockAlmacen');
        lista.innerHTML = '';

        // Filtrar elementos con stock menor a 5
        const bajoStock = data.filter(item => item.stock < 5);

        bajoStock.forEach(item => {
            const tipo = item.tipo.trim().toLowerCase();
            const nom = item.nombre.trim().toLowerCase();
            const stock = item.stock;

            const li = document.createElement('li');
            li.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1) + " " + nom.charAt(0).toUpperCase() + nom.slice(1) + " -> " + stock;
            lista.appendChild(li);
        });

    }

    //contador bajo stock
    contadorBajoStock(data) {
        const contador = document.getElementById('contadorBajoStock');
        contador.innerHTML = '';

        const bajoStock = data.filter(item => item.stock < 5); // Filtrar elementos con stock menor a 5
        contador.textContent = bajoStock.length; // Mostrar la cantidad de elementos con bajo stock

    }



}

// Inicializar la aplicación
let principal;
document.addEventListener('DOMContentLoaded', () => {
    principal = new Principal();
});
