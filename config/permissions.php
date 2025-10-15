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
        'orders' => [
            'label' => 'Gestão de pedidos',
            'abilities' => [
                'view' => 'Visualizar pedidos',
                'create' => 'Criar pedidos',
                'update' => 'Editar pedidos',
                'delete' => 'Excluir pedidos',
                'update_status' => 'Alterar status de pedidos',
                'export_pdf' => 'Exportar PDF de pedidos',
            ],
        ],
    ],
];
