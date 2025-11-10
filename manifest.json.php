<?php
require_once 'config.php';

// Set correct content type for JSON
header('Content-Type: application/json');

// Get base path
$basePath = BASE_PATH;

// Generate manifest with correct paths
$manifest = [
    "name" => "Cash Book - Money Manager",
    "short_name" => "Cash Book",
    "description" => "Track your cash flow, manage groups, and keep records of all transactions",
    "start_url" => $basePath . "/",
    "display" => "standalone",
    "background_color" => "#667eea",
    "theme_color" => "#667eea",
    "orientation" => "portrait-primary",
    "scope" => $basePath . "/",
    "icons" => [
        [
            "src" => $basePath . "/icons/icon-72x72.png",
            "sizes" => "72x72",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-96x96.png",
            "sizes" => "96x96",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-128x128.png",
            "sizes" => "128x128",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-144x144.png",
            "sizes" => "144x144",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-152x152.png",
            "sizes" => "152x152",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-192x192.png",
            "sizes" => "192x192",
            "type" => "image/png",
            "purpose" => "any maskable"
        ],
        [
            "src" => $basePath . "/icons/icon-384x384.png",
            "sizes" => "384x384",
            "type" => "image/png",
            "purpose" => "any"
        ],
        [
            "src" => $basePath . "/icons/icon-512x512.png",
            "sizes" => "512x512",
            "type" => "image/png",
            "purpose" => "any maskable"
        ]
    ],
    "categories" => ["finance", "productivity", "business"],
    "shortcuts" => [
        [
            "name" => "Add Cash In",
            "short_name" => "Cash In",
            "description" => "Add a cash in entry",
            "url" => $basePath . "/dashboard",
            "icons" => [
                [
                    "src" => $basePath . "/icons/icon-192x192.png",
                    "sizes" => "192x192"
                ]
            ]
        ],
        [
            "name" => "My Groups",
            "short_name" => "Groups",
            "description" => "View and manage groups",
            "url" => $basePath . "/groups",
            "icons" => [
                [
                    "src" => $basePath . "/icons/icon-192x192.png",
                    "sizes" => "192x192"
                ]
            ]
        ]
    ]
];

// Output JSON
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>

