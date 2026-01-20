<?php
 
function get_db_config(): array {
    return [
        'host' => 'localhost',
        'name' => 'library_db',
        'user' => 'root',
        'pass' => '',
    ];
}

function app_base_url(): string {
   
    return '/Project';
}
