<?php
namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Rezepte API',
    version: '1.0.0',
    description: 'API für Benutzer, Rezepte und Zutaten'
)]
#[OA\Server(
    url: '/',
//  description: 'Lokale Umgebung'
)]
final class OpenApi {}
