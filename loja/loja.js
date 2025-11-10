document.addEventListener('DOMContentLoaded', () => {
    // Caminho para o catálogo de produtos
    const catalogUrl = 'catalog.json';
    // Elementos do DOM
    const categoriesContainer = document.getElementById('categories');
    const productsContainer = document.getElementById('products');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxDescription = document.getElementById('lightboxDescription');
    const lightboxPrice = document.getElementById('lightboxPrice');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const productSelect = document.getElementById('productSelect');
    let products = [];

    // Verifica se os elementos essenciais existem
    if (!categoriesContainer || !productsContainer) {
        console.error('Faltam elementos HTML obrigatórios para categorias ou produtos.');
        return;
    }

    // Carrega categorias e produtos do catálogo
    fetch(catalogUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro HTTP! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const categories = data.categories;
            products = data.products;

            // Preenche a lista de categorias
            categories.forEach(category => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.dataset.category = category.id;
                listItem.textContent = category.name;
                categoriesContainer.appendChild(listItem);
            });

            // Preenche o dropdown de categorias
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryDropdown.appendChild(option);
            });

            // Preenche o dropdown de seleção de produtos
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.name;
                option.dataset.price = product.price;
                productSelect.appendChild(option);
            });

            // Mostra todos os produtos inicialmente
            displayProducts(products);

            // Filtra produtos por categoria ao clicar na lista
            categoriesContainer.addEventListener('click', event => {
                if (event.target.classList.contains('list-group-item')) {
                    const categoryId = event.target.dataset.category;
                    const filteredProducts = categoryId === 'all'
                        ? products
                        : products.filter(product => product.categoryId === categoryId);
                    displayProducts(filteredProducts);
                }
            });

            // Filtra produtos ao mudar o dropdown
            categoryDropdown.addEventListener('change', event => {
                const categoryId = event.target.value;
                const filteredProducts = categoryId === 'all'
                    ? products
                    : products.filter(product => product.categoryId === categoryId);
                displayProducts(filteredProducts);
            });
        })
        .catch(error => console.error('Erro ao carregar o catálogo:', error));

    // Mostra os produtos na interface
    function displayProducts(filteredProducts) {
        productsContainer.innerHTML = '';
        filteredProducts.forEach(product => {
            const productCard = `
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="${product.image}" class="card-img-top" alt="${product.name}">
                        <div class="card-body">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="card-text">${product.description}</p>
                            <p><strong>Preço:</strong> €${product.price.toFixed(2)}</p>
                            <input type="number" class="form-control mb-2 input-qty" min="1" value="1" style="width:90px;display:inline-block;">
                            <button class="btn btn-primary view-details" data-id="${product.id}">Ver Detalhes</button>
                        </div>
                    </div>
                </div>
            `;
            productsContainer.insertAdjacentHTML('beforeend', productCard);
        });

        // Adiciona eventos aos botões "Ver Detalhes"
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', event => {
                const productId = event.target.dataset.id;
                const product = products.find(p => p.id === productId);
                if (product) {
                    showProductDetails(product);
                }
            });
        });

        // Adiciona eventos aos botões "Adicionar ao Carrinho"
        document.querySelectorAll('#products .card').forEach(card => {
            if (!card.querySelector('.btn-add-cart')) {
                var btn = document.createElement('button');
                btn.className = 'btn btn-success btn-add-cart mt-2';
                btn.innerHTML = '<i class="fas fa-cart-plus"></i> Adicionar ao Carrinho';
                btn.onclick = function() {
                    // Obtém os dados do produto selecionado
                    const productId = card.querySelector('.view-details').getAttribute('data-id');
                    const productName = card.querySelector('.card-title').textContent;
                    const productPrice = card.querySelector('p strong + text, .card-text + p').textContent.match(/\d+[\.,]?\d*/)[0].replace(',', '.');
                    const qtyInput = card.querySelector('.input-qty');
                    const quantity = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10)) : 1;
                    // Cria e submete o formulário para adicionar ao carrinho
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'loja/carrinho.php';
                    form.style.display = 'none';
                    form.innerHTML = '<input type="hidden" name="add_to_cart" value="1">' +
                        '<input type="hidden" name="product_id" value="' + productId + '">' +
                        '<input type="hidden" name="product_name" value="' + productName + '">' +
                        '<input type="hidden" name="product_price" value="' + productPrice + '">' +
                        '<input type="hidden" name="quantity" value="' + quantity + '">';
                    document.body.appendChild(form);
                    form.submit();
                };
                card.querySelector('.card-body').appendChild(btn);
            }
        });
    }

    // Mostra os detalhes do produto no lightbox
    function showProductDetails(product) {
        lightboxTitle.textContent = product.name;
        lightboxImage.src = product.image;
        lightboxDescription.textContent = product.description;
        lightboxPrice.textContent = `€${product.price.toFixed(2)}`;
        $('#lightbox').modal('show'); // Modal do Bootstrap
    }
});
