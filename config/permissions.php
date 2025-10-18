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
        'raw_materials' => [
            'label' => 'Gestão de matérias-primas',
            'abilities' => [
                'view' => 'Visualizar matérias-primas',
                'create' => 'Criar matérias-primas',
                'update' => 'Editar matérias-primas',
                'delete' => 'Excluir matérias-primas',
            ],
        ],
        'almoxarifados' => [
            'label' => 'Gestão de almoxarifados',
            'abilities' => [
                'view' => 'Visualizar almoxarifados',
                'create' => 'Criar almoxarifados',
                'update' => 'Editar almoxarifados',
                'delete' => 'Excluir almoxarifados',
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
        'reasons' => [
            'label' => 'Motivos',
            'abilities' => [
                'view' => 'Visualizar motivos',
                'create' => 'Criar motivos',
                'update' => 'Editar motivos',
                'delete' => 'Excluir motivos',
            ],
        ],
        'machine_downtimes' => [
            'label' => 'Paradas de Máquina',
            'abilities' => [
                'view' => 'Visualizar paradas de máquina',
                'create' => 'Criar paradas de máquina',
                'update' => 'Editar paradas de máquina',
                'delete' => 'Excluir paradas de máquina',
            ],
        ],
        'operators' => [
            'label' => 'Gestão de operadores',
            'abilities' => [
                'view' => 'Visualizar operadores',
                'create' => 'Criar operadores',
                'update' => 'Editar operadores',
                'delete' => 'Excluir operadores',
            ],
        ],
    ],
];
