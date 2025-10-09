<?php

return [
    'resources' => [
        'clients' => [
            'label' => 'Gestão de clientes',
            'abilities' => [
                'view' => 'Visualizar clientes',
                'create' => 'Criar clientes',
                'update' => 'Editar clientes',
                'delete' => 'Excluir clientes',
            ],
        ],
        'products' => [
            'label' => 'Gestão de produtos',
            'abilities' => [
                'view' => 'Visualizar produtos',
                'create' => 'Criar produtos',
                'update' => 'Editar produtos',
                'delete' => 'Excluir produtos',
            ],
        ],
    ],
];
