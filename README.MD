# Desafio Backend
O objetivo deste desafio foi de classificar de forma assertiva a prioridade dos tickets. Os tickets originais se encontram no arquivo **tickets_original.json**, o arquivo **tickets.json** contém a prioridade e a pontuação dos tickets já calculados.

# Pesos e Cálculos
São considerados alguns pontos que refletem nos pesos, como por exemplo o assunto, última mensagem (ou primeira interação), data de contato e a mensagem em si. Algumas condições influenciam no peso de outros pontos.

# Ferramentas utilizadas
Como linguagem de programação para desenvolvimento da API foi utilizado o PHP, juntamente com o framework CodeIgniter para controle de rotas, helpers e estrutura.


## Como testar
Primeiramente, é necessário que se tenha algum servidor rodando apache e PHP. Para testar a ferramenta que classifica e calcula a pontuação de prioridades, basta subir o servidor e acessar via browser o endereço do servidor + a pasta que está instalado o projeto e /Algoritmo. Exemplo:

http://localhost/Desafio/Algoritmo

Para testar a API que expõe os resultados obtidos, sugiro que seja com o POSTMAN (https://www.getpostman.com/). Na pasta examples, estão imagens do postman com a URL da API.

# Autor
Emerson Andrei Almeida Corrrea