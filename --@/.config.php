<?php 

$_['.'] = \vnd\github__klude_org__pax_one\std\origin::class;
$_[\_::class] = [
    '*' => [
        'modules' => [
            'app' => true,
        ],
    ],
    'web' => [
        'modules' => [
        ],
    ],
    'cli' => [
        'modules' => [
        ]
    ],
    'api' => [
        'modules' => [
        ]
    ],
    'web--@' => [
        'modules' => [
        ]
    ],
    'cli--@' => [
        'modules' => [
        ]
    ],
];

$_[\_\db::class] = [
    '*' => [
        'HOSTNAME'=> 'localhost',
        'USERNAME'=> 'root',
        'PASSWORD'=> '.',
        'DATABASE'=> 'db-default',
    ]
];

$_[\_\auth::class] = [
    '*' => [
        'users' => [
            'admin'=> [ 
                'name' => 'Admin', 
                'password' => '`pass', 
                'panels' => ['*'], 
                'roles' => ['*'] 
            ],
            'developer'=> [ 
                'name' => 'Admin', 
                'password' => '`pass', 
                'panels' => ['*'], 
                'roles' => ['*'] 
            ],
        ]
    ]
];

$_[\_\composer::class] = [
    '*' => [
        'path' => 'composer'
    ],
    'web' => [
        'path' => 'composer-web'
    ],
    'cli' => [
        'path' => 'composer-cli'
    ],
];