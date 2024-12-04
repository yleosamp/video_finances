<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Vídeos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #202020;
            color: #B9B9B9;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        select, input, button {
            outline: none;
        }
        .price-tag {
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        .price-usd {
            background-color: #9FFEBB;
            color: #202020;
        }
        .price-brl {
            background-color: #E9FF69;
            color: #202020;
        }
        .price-total {
            background-color: #B974ED;
            color: #202020;
        }
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col p-8">
    <!-- Botão de Login/Registro -->
    <div class="self-end mb-8">
        <button id="loginButton" class="bg-[#3E62A5] text-[#ADC8FB] px-6 py-2 rounded-md text-lg font-medium hover:bg-[#3E62A5]/90 transition-colors">
            Login / Registro
        </button>
    </div>

    <div class="w-full max-w-md mx-auto space-y-4">
        <!-- Seletor Mês, Ano e Pessoas -->
        <div class="grid grid-cols-[1fr,auto,auto] gap-3">
            <select id="monthSelect" class="bg-[#313131] rounded-md px-6 py-2.5 text-lg appearance-none cursor-pointer">
                <option value="1">Janeiro</option>
                <option value="2">Fevereiro</option>
                <option value="3">Março</option>
                <option value="4">Abril</option>
                <option value="5">Maio</option>
                <option value="6">Junho</option>
                <option value="7">Julho</option>
                <option value="8">Agosto</option>
                <option value="9">Setembro</option>
                <option value="10">Outubro</option>
                <option value="11">Novembro</option>
                <option value="12">Dezembro</option>
            </select>
            <select id="yearSelect" class="bg-[#313131] rounded-md px-4 py-2.5 w-24 text-center appearance-none cursor-pointer">
                <!-- Anos serão adicionados via JavaScript -->
            </select>
            <div id="peopleCounter" class="bg-[#313131] rounded-md px-6 py-2.5 flex items-center cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
            </div>
        </div>

        <!-- Inputs -->
        <div class="grid grid-cols-[1fr,auto] gap-3">
            <input type="text" id="videoName" placeholder="Nome" class="bg-[#313131] rounded-md px-6 py-2.5">
            <div class="flex">
                <input type="text" id="videoPrice" placeholder="0" class="w-24 bg-[#313131] rounded-l-md px-6 py-2.5">
                <button id="currencyToggle" class="bg-[#E9FF69] text-black px-6 py-2.5 rounded-r-md font-medium min-w-[60px]">R$</button>
            </div>
        </div>

        <!-- Botão Adicionar -->
        <button id="addVideoButton" class="w-full bg-[#3E62A5] text-[#ADC8FB] py-3 rounded-md text-lg font-medium">
            Adicionar
        </button>

        <!-- Lista de Vídeos -->
        <div id="videosList" class="pt-8 space-y-4 hidden">
            <h2 id="monthTitle" class="text-2xl px-2"><strong>Janeiro</strong></h2>
            <div id="videosContainer" class="space-y-2">
                <!-- Os vídeos serão inseridos aqui dinamicamente -->
            </div>
            <!-- Linha separadora -->
            <div class="h-px bg-[#313131]"></div>
            <!-- Total -->
            <div class="flex items-center justify-between bg-[#313131] px-6 py-3 rounded-md">
                <span><strong>TOTAL</strong></span>
                <div class="flex items-center gap-3">
                    <span id="totalValue" class="price-tag price-total" onclick="toggleTotalCurrency()">R$ 0</span>
                    <button id="totalToggle" class="w-8 h-8 bg-[#B974ED] rounded-md" onclick="toggleTotalView()"></button>
                </div>
            </div>
            <div class="mt-4">
                <button id="exportExcelButton" class="w-full bg-[#4CAF50] text-white py-3 rounded-md text-lg font-medium hover:bg-[#4CAF50]/90 transition-colors">
                    Exportar para Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Notas -->
    <div id="notesModal" class="modal">
        <div class="bg-[#313131] rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl">Adicione uma nota a este vídeo...</h3>
                <button onclick="deleteVideo()" class="bg-[#FF696C] text-white px-4 py-2 rounded-md hover:bg-[#FF696C]/90 transition-colors">
                    Excluir
                </button>
            </div>
            <textarea id="videoNotes" class="w-full h-32 bg-[#202020] rounded-md p-4 mb-4"></textarea>
            <div class="flex items-center gap-3 bg-[#202020] p-3 rounded-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
                <input id="modalPeopleCount" type="number" min="1" value="1" class="w-12 bg-transparent text-center" onchange="updateModalPrice()">
                <span id="modalPrice" class="price-tag price-brl">R$ 1000</span>
            </div>
            <input type="hidden" id="currentVideoId">
            <button onclick="saveNotes()" class="w-full bg-[#3E62A5] text-[#ADC8FB] py-2 mt-4 rounded-md text-lg font-medium">
                Salvar
            </button>
        </div>
    </div>

    <!-- Modal de Autenticação -->
    <div id="authModal" class="modal">
        <div class="bg-[#313131] rounded-lg p-6 w-full max-w-md mx-4">
            <h3 id="authTitle" class="text-xl mb-4">Login</h3>
            <form id="authForm" class="space-y-4">
                <input type="email" placeholder="Email" class="w-full bg-[#202020] rounded-md px-4 py-2">
                <input type="password" placeholder="Senha" class="w-full bg-[#202020] rounded-md px-4 py-2">
                <button type="submit" class="w-full bg-[#3E62A5] text-[#ADC8FB] py-2 rounded-md">Entrar</button>
                <a href="#" id="toggleAuthMode" class="block text-center text-sm text-[#ADC8FB]">Criar uma conta</a>
            </form>
        </div>
    </div>

    <script>
        let exchangeRate = 5; // Taxa padrão para fallback
        let isLoginMode = true;
        let totalViewState = 'all'; // 'all', 'paid', 'unpaid'
        let currentMonthIndex = new Date().getMonth(); // Inicializa com o mês atual (0-11)
        const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        async function getCurrencyRates() {
            try {
                const response = await fetch('https://economia.awesomeapi.com.br/last/USD-BRL,BRL-USD');
                const data = await response.json();
                
                // Atualiza a taxa de câmbio
                exchangeRate = parseFloat(data.USDBRL.bid);
                
                return {
                    USD_BRL: exchangeRate,
                    BRL_USD: parseFloat(data.BRLUSD.bid)
                };
            } catch (error) {
                console.error('Erro ao obter taxas de câmbio:', error);
                
                // Retorna valores padrão caso ocorra um erro
                return {
                    USD_BRL: exchangeRate, // Mantém o valor padrão de fallback
                    BRL_USD: 1 / exchangeRate // Usa o inverso para a taxa BRL para USD
                };
            }
        }

        // Exemplo de uso
        getCurrencyRates().then(rates => {
            console.log('Taxas de câmbio:', rates);
        });


        async function updateExchangeRates() {
            const rates = await getCurrencyRates();
            exchangeRate = rates.USD_BRL;
        }

        async function handleAuth(e) {
            e.preventDefault();
            const email = document.querySelector('#authForm input[type="email"]').value;
            const password = document.querySelector('#authForm input[type="password"]').value;

            const formData = new FormData();
            formData.append('action', isLoginMode ? 'login' : 'register');
            formData.append('email', email);
            formData.append('password', password);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    authModal.classList.remove('active');
                    document.getElementById('videosList').classList.remove('hidden');
                    loginButton.textContent = 'Sair';
                    loadVideos(document.getElementById('monthSelect').selectedIndex + 1);
                    
                    if (!isLoginMode) {
                        const loginFormData = new FormData();
                        loginFormData.append('action', 'login');
                        loginFormData.append('email', email);
                        loginFormData.append('password', password);
                        await fetch('api.php', {
                            method: 'POST',
                            body: loginFormData
                        });
                    }
                } else {
                    alert(data.message || 'Erro na autenticação');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro na autenticação');
            }
        }

        async function checkAuth() {
            const formData = new FormData();
            formData.append('action', 'check_auth');
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    loginButton.textContent = 'Sair';
                    document.getElementById('videosList').classList.remove('hidden');
                    
                    // Inicializar com janeiro do ano atual
                    const currentYear = new Date().getFullYear();
                    yearSelect.value = currentYear;
                    monthSelect.value = "1";
                    
                    // Atualizar título e carregar vídeos
                    const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                    document.getElementById('monthTitle').textContent = `${months[0]} ${currentYear}`;
                    await loadVideos(1, currentYear);
                } else {
                    loginButton.textContent = 'Login / Registro';
                    document.getElementById('videosList').classList.add('hidden');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        async function addVideo() {
            const name = document.getElementById('videoName').value;
            const price = document.getElementById('videoPrice').value;
            const currency = document.getElementById('currencyToggle').textContent.trim();
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;
            const peopleCount = document.getElementById('peopleCounter').querySelector('input')?.value || 1;

            const formData = new FormData();
            formData.append('action', 'add_video');
            formData.append('name', name);
            formData.append('price', price);
            formData.append('currency', currency === 'R$' ? 'BRL' : 'USD');
            formData.append('month', month);
            formData.append('year', year);
            formData.append('people_count', peopleCount);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('videoName').value = '';
                    document.getElementById('videoPrice').value = '';
                    loadVideos(month, year);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        async function loadVideos(month, year) {
            const formData = new FormData();
            formData.append('action', 'get_videos');
            formData.append('month', month);
            formData.append('year', year);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    updateVideosList(data.videos);
                    const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                    document.getElementById('monthTitle').textContent = `${months[month - 1]} ${year}`;
                }
            } catch (error) {
                console.error('Erro ao carregar vídeos:', error);
            }
        }

        function updateVideosList(videos) {
            const container = document.getElementById('videosContainer');
            let html = '';
            let totalBRL = 0;
            let totalUSD = 0;

            // Filtrar vídeos baseado no estado do total
            const filteredVideos = videos.filter(video => {
                // Converter para número para garantir a comparação correta
                const isPaid = parseInt(video.is_paid);
                
                if (totalViewState === 'paid') return isPaid === 0;      // Mostra pagos (is_paid = 0)
                if (totalViewState === 'unpaid') return isPaid === 1;    // Mostra no pagos (is_paid = 1)
                return true;    // Mostra todos quando totalViewState === 'all'
            });

            filteredVideos.forEach(video => {
                const currencySymbol = video.currency === 'USD' ? 'U$' : 'R$';
                const priceClass = video.currency === 'USD' ? 'price-usd' : 'price-brl';
                const isPaid = parseInt(video.is_paid) === 0;

                if (video.currency === 'USD') {
                    totalUSD += parseFloat(video.price);
                    totalBRL += parseFloat(video.price) * exchangeRate;
                } else {
                    totalBRL += parseFloat(video.price);
                    totalUSD += parseFloat(video.price) / exchangeRate;
                }

                html += `
                    <div class="flex items-center justify-between bg-[#313131] px-6 py-3 rounded-md cursor-pointer">
                        <span onclick="openModal(${video.id}, '${video.name}', ${video.price}, '${video.currency}', ${video.people_count})">${video.name}</span>
                        <div class="flex items-center gap-3">
                            <span class="price-tag ${priceClass}" onclick="toggleCurrency(this, ${video.id})">${currencySymbol} ${video.price}</span>
                            <button class="w-8 h-8 bg-[${isPaid ? '#9FFEBB' : '#FF696C'}] rounded-md" onclick="togglePaymentStatus(this, ${video.id})"></button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            updateTotal(totalBRL, totalUSD);
        }

        function updateTotal(totalBRL, totalUSD) {
            const totalElement = document.getElementById('totalValue');
            const currentCurrency = totalElement.textContent.includes('R$') ? 'BRL' : 'USD';
            totalElement.textContent = `${currentCurrency === 'BRL' ? 'R$' : 'U$'} ${(currentCurrency === 'BRL' ? totalBRL : totalUSD).toFixed(2)}`;
        }

        function toggleTotalCurrency() {
            const totalElement = document.getElementById('totalValue');
            const isUSD = totalElement.textContent.includes('U$');
            const value = parseFloat(totalElement.textContent.split(' ')[1]);
            
            if (isUSD) {
                totalElement.textContent = `R$ ${(value * exchangeRate).toFixed(2)}`;
                totalElement.classList.remove('price-usd');
                totalElement.classList.add('price-brl');
            } else {
                totalElement.textContent = `U$ ${(value / exchangeRate).toFixed(2)}`;
                totalElement.classList.remove('price-brl');
                totalElement.classList.add('price-usd');
            }
        }

        // Event Listeners
        document.getElementById('addVideoButton').addEventListener('click', addVideo);
        document.getElementById('monthSelect').addEventListener('change', function() {
            const selectedMonth = this.selectedIndex + 1;
            loadVideos(selectedMonth);
        });

        document.getElementById('peopleCounter').addEventListener('click', function() {
            this.innerHTML = '<input type="number" min="1" value="1" class="w-12 bg-transparent text-center">';
            this.querySelector('input').focus();
        });

        document.getElementById('currencyToggle').addEventListener('click', function() {
            this.textContent = this.textContent === 'R$' ? 'U$' : 'R$';
            this.classList.toggle('bg-[#E9FF69]');
            this.classList.toggle('bg-[#9FFEBB]');
        });

        // Configurar anos no select e inicializar página
        document.addEventListener('DOMContentLoaded', async function() {
            const yearSelect = document.getElementById('yearSelect');
            const monthSelect = document.getElementById('monthSelect');
            const currentYear = new Date().getFullYear();
            const years = new Set([2024, currentYear, currentYear + 1]);
            
            // Configurar anos
            yearSelect.innerHTML = '';
            Array.from(years).sort().forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
            
            // Definir valores iniciais
            yearSelect.value = currentYear;
            monthSelect.value = "1"; // Janeiro
            
            // Event listeners
            yearSelect.addEventListener('change', function() {
                loadVideos(monthSelect.value, this.value);
            });
            
            monthSelect.addEventListener('change', function() {
                loadVideos(this.value, yearSelect.value);
            });

            // Inicializar com janeiro do ano atual
            updateMonthTitle(1, currentYear);
            await loadVideos(1, currentYear);
        });

        function updateMonthTitle(month, year) {
            const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            document.getElementById('monthTitle').textContent = `${months[month - 1]} ${year}`;
        }

        // Auth Modal
        const loginButton = document.getElementById('loginButton');
        const authModal = document.getElementById('authModal');
        const authTitle = document.getElementById('authTitle');
        const authForm = document.getElementById('authForm');
        const toggleAuthMode = document.getElementById('toggleAuthMode');

        loginButton.addEventListener('click', () => {
            if (loginButton.textContent === 'Sair') {
                const formData = new FormData();
                formData.append('action', 'logout');
                fetch('api.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    location.reload();
                });
            } else {
                authModal.classList.add('active');
            }
        });

        authModal.addEventListener('click', (e) => {
            if (e.target === authModal) {
                authModal.classList.remove('active');
            }
        });

        toggleAuthMode.addEventListener('click', (e) => {
            e.preventDefault();
            isLoginMode = !isLoginMode;
            authTitle.textContent = isLoginMode ? 'Login' : 'Registro';
            authForm.querySelector('button').textContent = isLoginMode ? 'Entrar' : 'Registrar';
            toggleAuthMode.textContent = isLoginMode ? 'Criar uma conta' : 'Já tenho uma conta';
        });

        authForm.addEventListener('submit', handleAuth);

        // Funções para o modal e manipulação de vídeos
        async function openModal(videoId, videoName, price, currency, people) {
            const modal = document.getElementById('notesModal');
            const modalPrice = document.getElementById('modalPrice');
            const modalPeopleCount = document.getElementById('modalPeopleCount');
            const notesTextarea = document.getElementById('videoNotes');
            const currentVideoId = document.getElementById('currentVideoId');
            
            currentVideoId.value = videoId;
            
            // Buscar notas do vídeo
            const formData = new FormData();
            formData.append('action', 'get_notes');
            formData.append('video_id', videoId);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    notesTextarea.value = data.notes || '';
                }
            } catch (error) {
                console.error('Erro:', error);
            }
            
            // Configurar preço e pessoas
            modalPrice.textContent = `${currency === 'USD' ? 'U$' : 'R$'} ${(price / people).toFixed(2)}`;
            modalPrice.className = `price-tag ${currency === 'USD' ? 'price-usd' : 'price-brl'}`;
            modalPeopleCount.value = people;
            modalPeopleCount.dataset.originalPrice = price;
            modalPeopleCount.dataset.currency = currency;
            
            modal.classList.add('active');
        }

        function updateModalPrice() {
            const modalPrice = document.getElementById('modalPrice');
            const modalPeopleCount = document.getElementById('modalPeopleCount');
            const originalPrice = parseFloat(modalPeopleCount.dataset.originalPrice);
            const currency = modalPeopleCount.dataset.currency;
            const people = parseInt(modalPeopleCount.value);
            
            const newPrice = originalPrice / people;
            modalPrice.textContent = `${currency === 'USD' ? 'U$' : 'R$'} ${newPrice.toFixed(2)}`;
        }

        async function saveNotes() {
            const videoId = document.getElementById('currentVideoId').value;
            const notes = document.getElementById('videoNotes').value;
            
            const formData = new FormData();
            formData.append('action', 'save_notes');
            formData.append('video_id', videoId);
            formData.append('notes', notes);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('notesModal').classList.remove('active');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        async function toggleCurrency(element, videoId) {
            const value = parseFloat(element.textContent.split(' ')[1]);
            const isUSD = element.textContent.includes('U$');
            const newCurrency = isUSD ? 'BRL' : 'USD';
            
            const formData = new FormData();
            formData.append('action', 'update_currency');
            formData.append('video_id', videoId);
            formData.append('currency', newCurrency);
            formData.append('price', value);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    if (isUSD) {
                        element.textContent = `R$ ${(value * exchangeRate).toFixed(2)}`;
                        element.classList.remove('price-usd');
                        element.classList.add('price-brl');
                    } else {
                        element.textContent = `U$ ${(value / exchangeRate).toFixed(2)}`;
                        element.classList.remove('price-brl');
                        element.classList.add('price-usd');
                    }
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        async function togglePaymentStatus(button, videoId) {
            const formData = new FormData();
            formData.append('action', 'toggle_payment');
            formData.append('video_id', videoId);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    button.classList.toggle('bg-[#FF696C]');
                    button.classList.toggle('bg-[#9FFEBB]');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        // Fechar modal ao clicar fora
        document.getElementById('notesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });

        function closeModal() {
            document.getElementById('notesModal').classList.remove('active');
        }

        // Chamar checkAuth quando a página carregar
        document.addEventListener('DOMContentLoaded', async function() {
            await checkAuth();
        });

        function toggleTotalView() {
            const button = document.getElementById('totalToggle');
            const currentMonth = document.getElementById('monthSelect').value;
            const currentYear = document.getElementById('yearSelect').value;

            switch(totalViewState) {
                case 'all':
                    totalViewState = 'paid';
                    button.classList.remove('bg-[#B974ED]');
                    button.classList.add('bg-[#9FFEBB]');
                    break;
                case 'paid':
                    totalViewState = 'unpaid';
                    button.classList.remove('bg-[#9FFEBB]');
                    button.classList.add('bg-[#FF696C]');
                    break;
                case 'unpaid':
                    totalViewState = 'all';
                    button.classList.remove('bg-[#FF696C]');
                    button.classList.add('bg-[#B974ED]');
                    break;
            }

            loadVideos(currentMonth, currentYear);
        }

        document.getElementById('exportExcelButton').addEventListener('click', async function() {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;
            
            const formData = new FormData();
            formData.append('month', month);
            formData.append('year', year);
            
            try {
                const response = await fetch('export.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `videos_${month}_${year}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                }
            } catch (error) {
                console.error('Erro ao exportar:', error);
                alert('Erro ao exportar para Excel');
            }
        });

        async function deleteVideo() {
            const videoId = document.getElementById('currentVideoId').value;
            
            if (!confirm('Tem certeza que deseja excluir este vídeo?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_video');
            formData.append('video_id', videoId);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    // Fechar modal
                    document.getElementById('notesModal').classList.remove('active');
                    
                    // Recarregar lista de vídeos com o mês e ano atuais
                    const month = currentMonthIndex + 1;
                    const year = document.getElementById('yearSelect').value;
                    await loadVideos(month, year);
                } else {
                    alert('Erro ao excluir o vídeo');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir o vídeo');
            }
        }
    </script>
</body>
</html>