<?php
use Cake\Routing\Router;

Router::plugin('CakeFabricate', function ($routes) {
    $routes->fallbacks();
});
