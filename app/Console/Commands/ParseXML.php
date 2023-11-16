<?php

namespace App\Console\Commands;

use App\Actions\Product\ProductUpdateOrCreateFromDataAction;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;
use Psr\Log\LoggerInterface;

class ParseXML extends Command
{

    protected $signature = 'product:parse-xml';

    protected $description = 'Parse and update products from external XML';

    #[NoReturn] public function handle(
        ProductUpdateOrCreateFromDataAction $action,
        LoggerInterface $logger,
        Client $client
    ): void {
        $action($logger, $client);
    }
}
