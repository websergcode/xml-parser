<?php

namespace App\Actions\Product;

use App\Models\Product;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use XMLReader;

class ProductUpdateOrCreateFromDataAction
{
    public function __invoke(LoggerInterface $logger, Client $client, $chunkLimit = 50): void
    {
        $this->parseFile($client, $logger, $chunkLimit);
    }

    public function updateOrCreateProducts(array $productsData, LoggerInterface $logger): void
    {
        foreach ($productsData as $productData) {
            try {
                $images = $productData['images'];
                $productCode = $productData['product_code'];
                unset($productData['images'], $productData['product_code']);

                DB::transaction(static function () use ($images, $productData, $productCode, $logger) {
                    $product = Product::updateOrCreate(
                        [
                            'product_code' => $productCode,
                        ],
                        $productData
                    );

                    foreach ($images as $url) {
                        try {
                            $product
                                ->images()
                                ->updateOrCreate(
                                    [
                                        'url' => $url,
                                    ]
                                );
                        } catch (Exception $exception) {
                            $logger->error($exception->getMessage());
                        }
                    }
                });
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            }
        }
    }

    public function parseFile(Client $client, LoggerInterface $logger, int $chunkLimit): void
    {
        Product::query()
            ->update([
                'was_in_unloading' => false,
            ]);

        $url = 'https://cdn.iport.ru/files/xml/TradeIn_markdown.xml';

        $reader = XMLReader::open($url);

        $data = [];

        while ($reader->read()) {
            try {
                if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'Товар') {
                    if (count($data) >= $chunkLimit) {
                        $completeData = $this->completeData($data, $client, $logger);
                        $this->updateOrCreateProducts($completeData, $logger);

                        $data = [];
                    }

                    $product = [];

                    while ($reader->read()) {
                        try {
                            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'Товар') {
                                break;
                            }

                            if (
                                ($reader->nodeType === XMLReader::ELEMENT)
                                && Product::isRegisteredField($reader->localName, $logger)
                            ) {
                                $field = Product::getEnglishFieldName($reader->localName);
                                $reader->read();
                                $value = trim($reader->value);
                                $product[$field] = $value;
                            }
                        } catch (Exception $exception) {
                            $logger->error($exception->getMessage());
                        }
                    }

                    if ($product['is_trade_in'] === 'Да') {
                        $data[] = $product;
                    }
                }
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            }
        }

        $reader->close();

        Product::query()
            ->where('was_in_unloading', false)
            ->update([
                'active' => false,
            ]);
    }

    public function completeData(array $data, Client $client, LoggerInterface $logger): array
    {
        $completeData = [];

        foreach ($data as $productData) {
            try {
                $completeProduct = $productData;
                $completeProduct['original_price'] = null;
                $completeProduct['name'] = null;
                $completeProduct['images'] = [];
                $completeProduct['was_in_unloading'] = true;

                $webData = Product::getWebData($productData['product_code'], $client, $logger);

                $success = isset($webData['status']) && ($webData['status'] === 'success');

                if ($success) {
                    if (isset($webData['data']['TITLE']) && is_string($webData['data']['TITLE'])) {
                        $completeProduct['name'] = $webData['data']['TITLE'];
                    } else {
                        $success = false;
                    }

                    if (isset($webData['data']['PRICE']['VALUE']) && is_int($webData['data']['PRICE']['VALUE'])) {
                        $completeProduct['original_price'] = $webData['data']['PRICE']['VALUE'];
                    } else {
                        $success = false;
                    }

                    if (isset($webData['data']['IMAGES']) && is_array($webData['data']['IMAGES'])) {
                        $completeProduct['images'] = $webData['data']['IMAGES'];
                    } else {
                        $success = false;
                    }
                }

                if (!$success) {
                    $completeProduct['active'] = false;
                } else {
                    $completeProduct['active'] = true;
                }

                $completeData[] = $completeProduct;
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            }
        }

        return $completeData;
    }
}