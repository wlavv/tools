<?php

return [
    'default' => 'Cria uma descricao comercial clara, profissional e pronta para ecommerce. Estrutura o texto para apresentar o produto, explicar o seu valor, indicar uso/beneficio principal e fechar com uma frase curta de confianca. Evita inventar informacao que nao esteja nos dados do anuncio.',

    'stores' => [
        'tcg-collectors' => [
            'default' => 'Cria uma descricao para uma carta ou produto colecionavel TCG. Valoriza edicao, referencia, estado, raridade, colecionabilidade e utilidade para jogadores/colecionadores, sem inventar estado fisico, grading ou stock.',
            'categories' => [
                'magic-the-gathering' => 'Cria uma descricao para Magic: The Gathering com tom de colecionador. Destaca nome da carta, set/edicao, numero de colecionador, tipo de carta, raridade e interesse para deckbuilding ou colecao quando esses dados existirem.',
                'single-cards' => 'Cria uma descricao para carta individual TCG. O texto deve ser direto, confiavel e adequado a pagina de produto, reforcando identificacao da carta, edicao e valor para colecionadores.',
                'sealed-products' => 'Cria uma descricao para produto selado TCG. Destaca formato do produto, experiencia de abertura, potencial de colecao e adequacao para jogadores/colecionadores, sem prometer conteudo especifico nao confirmado.',
            ],
        ],
    ],
];
