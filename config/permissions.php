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
        'leads' => [
            'label' => 'Gestão de leads',
            'abilities' => [
                'view' => 'Visualizar leads',
                'create' => 'Criar leads',
                'update' => 'Editar leads',
                'delete' => 'Excluir leads',
            ],
        ],
        'opportunities' => [
            'label' => 'Gestão de oportunidades',
            'abilities' => [
                'view' => 'Visualizar oportunidades',
                'create' => 'Criar oportunidades',
                'update' => 'Editar oportunidades',
                'delete' => 'Excluir oportunidades',
            ],
        ],
        'sectors' => [
            'label' => 'Gestão de setores',
            'abilities' => [
                'view' => 'Visualizar setores',
                'create' => 'Criar setores',
                'update' => 'Editar setores',
                'delete' => 'Excluir setores',
            ],
        ],
        'machines' => [
            'label' => 'Gestão de máquinas',
            'abilities' => [
                'view' => 'Visualizar máquinas',
                'create' => 'Criar máquinas',
                'update' => 'Editar máquinas',
                'delete' => 'Excluir máquinas',
            ],
        ],
        'reason_types' => [
            'label' => 'Tipos de Motivos',
            'abilities' => [
                'view' => 'Visualizar tipos de motivos',
                'create' => 'Criar tipos de motivos',
                'update' => 'Editar tipos de motivos',
                'delete' => 'Excluir tipos de motivos',
            ],
        ],
    ],
];
