<?php

return [
    // Unsure about the need for those dependencies at this point:
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@causal/staffdirectory/' => 'EXT:staffdirectory/Resources/Public/ECMAScript6/',
    ],
];
