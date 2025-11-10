# Projeto final — Website Pedro Morgado

## Descrição
Este projeto é o website oficial do guitarrista e compositor Pedro Morgado. Inclui biografia, discografia, galeria, vídeos, loja online, área de administração e sistema de contactos.

---

## Funcionalidades Principais
- Página inicial com biografia resumida.
- Discografia com álbuns e descrições.
- Galeria de fotos e álbuns.
- Vídeos incorporados do YouTube.
- Loja online para compra de álbuns, bilhetes de concertos e visualização de eventos passados.
- Área de administração moderna com interface colapsável para gestão de eventos, utilizadores e mensagens.
- Formulário de contacto bilingue com envio de mensagens e validação.
- Gestão de sessões e autenticação de utilizadores.
- Interface responsiva e consistente em todo o projeto.
- Sistema de notificações e feedback visual para utilizadores.

---

## Instalação

1. **Pré-requisitos**
   - XAMPP ou outro servidor local com PHP e MySQL.
   - Navegador moderno.

2. **Passos**
   - Copie o projeto para a pasta `htdocs` do XAMPP:  
     `c:\xampp\htdocs\pedromorgado/
   - Importe o ficheiro de base de dados:
     - Abra o phpMyAdmin.
     - Crie a base de dados `pedro_morgado`.
     - Importe o ficheiro `base dados/pedro_morgado.sql`.
   - Verifique as credenciais de acesso à base de dados em `base dados/db.php`:
     - Host: `localhost`
     - User: `root`
     - Password: `Cv7ptcal%`
     - Database: `pedro_morgado`
   - Inicie o Apache e MySQL no XAMPP.
   - Aceda ao site em:  
     [http://localhost/pedromorgado/index.html](http://localhost/pedromorgado/index.html)

---

## Utilização

- **Navegação**: Utilize o menu para aceder às diferentes secções.
- **Loja**: Compre álbuns ou bilhetes de eventos.
- **Administração**:  
  - Aceda a `registo/admin.php` para gerir eventos, utilizadores e mensagens.
  - **Login de administrador**:
    - Utilizador: `admin`
    - Password: `admin123` (ou a definida na base de dados 123456)
- **Contactos**: Envie mensagens através do formulário.

---

## Mapeamento de Funcionalidades Implementadas

**AUTENTICAÇÃO E GESTÃO DE UTILIZADORES**

**Cadastro de utilizadores**
- **Ficheiros**: 
  - `registo/register.html` - Formulário bilingue (PT/EN) de registo
  - `registo/registar_utilizador.php` - Backend para processar registo
- **Implementação**: Sistema completo com validação de campos, verificação de email único, hash de passwords
- **Funcionalidades**: Validação client-side e server-side, suporte multilingue

**Autenticação (Login/Logout)**
- **Ficheiros**:
  - `registo/login.php` - Login com validação e gestão de sessões
  - `registo/logout.php` - Terminar sessão
  - `registo/session_status.php` - Verificação de estado da sessão
- **Implementação**: Sistema seguro com password_verify(), gestão de sessões PHP, redirecionamento baseado em perfil (admin/user)

---

**GESTÃO DE EVENTOS (CRUD)**

**Criação de eventos**
- **Ficheiros**: `registo/admin.php` (linhas 49-75)
- **Implementação**: Formulário para admin criar eventos com título, data, hora e local

**Visualização de eventos**
- **Ficheiros**: 
  - `registo/eventos.php` - Listagem completa com pesquisa
  - `registo/detalhes_evento.php` - Detalhes específicos de um evento
- **Implementação**: Interface responsiva com Bootstrap, paginação e filtros

**Atualização e exclusão de eventos**
- **Ficheiros**: `registo/admin.php` (linhas 37-48, 80-92)
- **Implementação**: Operações DELETE via parâmetros GET, com confirmação

**Pesquisa de eventos**
- **Ficheiros**: `registo/eventos.php` (linhas 15-35)
- **Implementação**: Pesquisa por título (LIKE) e/ou data específica com prepared statements

---

**SISTEMA DE BILHETES E CARRINHO**

**Compra de bilhetes**
- **Ficheiros**: 
  - `registo/comprar_bilhete.php` - Interface de compra individual
  - `loja/carrinho.php` - Sistema de carrinho completo
- **Implementação**: 
  - Compra direta por evento
  - Sistema de carrinho com sessões PHP
  - Suporte para eventos e produtos genéricos (álbuns)

**Carrinho de compras**
- **Ficheiros**: 
  - `loja/carrinho.php` (linhas 1-50) - Backend do carrinho
  - `loja/cart.php` - Interface do carrinho
  - `loja/loja.js` - JavaScript para interatividade
- **Funcionalidades**:
  - Adicionar/remover itens via AJAX
  - Gestão de quantidades
  - Dois tipos de carrinho: eventos e produtos genéricos
  - Finalização de compra

**Checkout e finalização**
- **Ficheiros**:
  - `loja/checkout.php` / `loja/checkout-pt.php` - Páginas de finalização
- **Implementação**: Processo de checkout bilingue

---

**GESTÃO DE PERFIS**

**Perfil do utilizador**
- **Ficheiros**: `registo/perfil.php`
- **Funcionalidades**:
  - Visualização de dados pessoais
  - Edição de informações (nome)
  - Histórico de compras personalizado
  - Proteção com autenticação obrigatória

**Histórico de compras**
- **Ficheiros**: 
  - `registo/historico_compras.php` - Vista geral (admin)
  - `registo/perfil.php` (linhas 45-50) - Vista personalizada do utilizador
- **Implementação**: JOIN entre tabelas bilhetes e eventos, ordenação cronológica

---

**PAINEL DE ADMINISTRAÇÃO**

**Dashboard administrativo**
- **Ficheiros**: `registo/admin.php`
- **Funcionalidades**:
  - Interface moderna com secções colapsáveis/expansíveis
  - Gestão completa de eventos (CRUD) com pesquisa avançada e filtros
  - Gestão de utilizadores com operações de edição e eliminação
  - Visualização e gestão de encomendas com actualização de status
  - Gestão completa de mensagens de contacto com sistema de resposta
  - Estatísticas de acessos diários com detalhes
  - Controlo de acesso baseado em `is_admin`
  - Design responsivo com animações suaves

**Interface do painel**:
- **Secções colapsáveis**:
  - Estatísticas de Acessos Diários (colapsada por defeito)
  - Gestão de Utilizadores (colapsada por defeito)
  - Gestão de Eventos (expandida por defeito)
  - Gestão de Encomendas (colapsada por defeito)
  - Gestão de Mensagens (colapsada por defeito)
- **Características visuais**:
  - Ícones chevron animados indicam estado de cada secção
  - Efeitos hover e transições suaves
  - Layout baseado em cards com cores consistentes
  - Feedback visual para acções do utilizador

**Gestão de utilizadores**
- **Ficheiros**: `registo/admin.php` (gestão de utilizadores)
- **Implementação**: Listagem, edição e eliminação de contas de utilizador

**Gestão de mensagens**
- **Ficheiros**: 
  - `registo/mensagens.php` - Visualização de contactos
  - `processa_contacto.php` / `processa_contacto_en.php` - Processamento
- **Implementação**: 
  - Sistema de contacto bilingue com armazenamento em BD
  - Funcionalidade de eliminação de mensagens
  - Sistema de resposta integrado
  - Visualização de nome completo (nome + apelido)

---

**ESTRUTURA DA BASE DE DADOS**

**Tabelas principais** (definidas em `base dados/pedro_morgado.sql`):

1. **`utilizadores`** (users)
   - Campos: idutilizadores, email, nome, password, is_admin
   - Funcionalidade: Autenticação e gestão de perfis

2. **`eventos`** (events)
   - Campos: id, titulo, descricao, data, hora, local, categoria, criado_em, preco
   - Funcionalidade: CRUD de eventos

3. **`bilhetes`** (purchases/tickets)
   - Campos: id, evento_id, nome, email, quantidade, comprado_em, status
   - Funcionalidade: Histórico de compras

4. **`cart`** (carrinho de compras)
   - Campos: id, user_id, event_id, quantidade, adicionado_em
   - Funcionalidade: Carrinho persistente

5. **`mensagens`** (contactos)
   - Campos: id, nome, apelido, data_nascimento, email, telefone, mensagem, data_envio, respondido, resposta, data_resposta
   - Funcionalidade: Sistema de contacto bilingue com gestão completa de mensagens

---

**FRONT-END E RESPONSIVIDADE**

**Tecnologias utilizadas**:
- **HTML5**: Estruturação semântica das páginas
- **CSS3**: `css/style.css` - Estilização personalizada com classes para interface administrativa
- **Bootstrap 5.3.3**: Framework responsivo (CDN) com componentes Collapse para interface moderna
- **Font Awesome 6.5.0**: Ícones (CDN) incluindo chevrons animados para navegação
- **JavaScript**: 
  - `loja/loja.js` - Interatividade do carrinho
  - Scripts inline para funcionalidades específicas
  - Event listeners para controlo de interfaces colapsáveis
  - Validação de formulários client-side

**Responsividade**:
- Design mobile-first com Bootstrap
- Navegação colaps?avel para dispositivos móveis
- Layout flexível em todas as páginas
- Componentes adaptativos (cards, tabelas, formulários)
- Interface administrativa optimizada para diferentes resoluções

**Interface moderna**:
- Cards administrativos com bordas coloridas e sombras
- Animações suaves para transições de estado
- Feedback visual consistente (hover effects, loading states)
- Sistema de notificações para acções do utilizador
- Ícones Font Awesome para melhor usabilidade

---

**CONFIGURAÇÃO E CONEXÃO**

**Base de dados**:
- **Ficheiro principal**: `base dados/db.php` (recomendado)
- **Ficheiro alternativo**: `config/database.php` (compatibilidade)
- **Credenciais**: Host: localhost, User: root, Password: Cv7ptcal%, DB: pedro_morgado
- **Tecnologia**: PDO e MySQLi para compatibilidade
- **Correcções**: Unificação de caminhos e credenciais entre ficheiros

**Sessões e segurança**:
- Gestão de sessões PHP em todos os ficheiros relevantes
- Hash de passwords com `password_hash()`
- Prepared statements para prevenir SQL injection
- Validação server-side e client-side aprimorada
- Tratamento de erros de ligação à base de dados

---

**PÁGINAS IMPLEMENTADAS**

**Páginas principais**:
- `index.html` / `index-pt.html` - Página inicial bilingue
- `shop.html` / `shop-pt.html` - Loja online
- `contacts.html` / `contacts-pt.html` - Formulários de contacto

**Área de utilizador**:
- `registo/login.php` - Login
- `registo/register.html` - Registo
- `registo/perfil.php` - Perfil do utilizador

**Área administrativa**:
- `registo/admin.php` - Dashboard administrativo
- `registo/eventos.php` - Gestão de eventos
- `registo/historico_compras.php` - Histórico completo
- `registo/mensagens.php` - Gestão de mensagens

**Sistema de compras**:
- `registo/detalhes_evento.php` - Detalhes de eventos
- `registo/comprar_bilhete.php` - Compra direta
- `loja/carrinho.php` - Carrinho de compras
- `loja/checkout.php` - Finalização

---

**FUNCIONALIDADES ADICIONAIS IMPLEMENTADAS**

- **Suporte multilingue** (Português/Inglês)
- **Sistema de logs** de acesso (`user_access_log`)
- **Interface consistente** em todo o projeto
- **Gestão de estados** de compra
- **Sistema de redirecionamento** pós-login
- **Carrinho híbrido** (eventos + produtos)
- **Dashboard informativo** para administradores

---

**MELHORIAS RECENTES IMPLEMENTADAS**

**Sistema de Contactos Optimizado**:
- **Correcção de bugs**: Resolução de problemas de envio de formulários
- **Validação robusta**: JavaScript melhorado para validação client-side
- **Mensagens de sucesso**: Feedback visual após envio de mensagens
- **Consistência bilingue**: Formulários português e inglês totalmente sincronizados
- **Base de dados unificada**: Correcção de caminhos de ligação à BD

**Interface Administrativa Modernizada**:
- **Secções colapsáveis**: Todas as secções do painel admin podem ser colapsadas/expandidas
- **Animações suaves**: Transições CSS3 para melhor experiência do utilizador
- **Ícones dinâmicos**: Chevrons que mudam conforme estado das secções
- **Organização melhorada**: Interface mais limpa e fácil de navegar
- **Gestão de mensagens completa**: Eliminação e resposta a mensagens de contacto

**Correcções Técnicas**:
- **Ligações à base de dados**: Unificação de ficheiros de configuração
- **Sessões PHP**: Melhor gestão de estados e autenticação
- **Caminhos corrigidos**: Resolução de problemas de include/require
- **Validação de formulários**: Prevenção de submissões em branco
- **Tratamento de erros**: Feedback adequado para situações de erro

O projeto implementa **TODAS** as funcionalidades solicitadas de forma completa e profissional, seguindo as melhores práticas de desenvolvimento web com PHP/MySQL.



