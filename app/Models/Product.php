<?php

namespace App\Models;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Psr\Log\LoggerInterface;

class Product extends Model
{

    protected $fillable = [
        "active",
        "was_in_unloading",
        "battery_is_new",
        "condition",
        "contents_box",
        "discount_reason_extended",
        "discounted_product_code",
        "engineer_comment",
        "equipment_adapter",
        "equipment_cable",
        "equipment_strap",
        "is_trade_in",
        "kit",
        "name",
        "operability",
        "original_price",
        "price",
        "product_code",
        "region",
        "serial_number",
        "warehouse",
        "warehouse_name",
        "warranty_end_date",
        'created_at',
        'updated_at',
    ];


    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'model');
    }

    public static function getRegisteredFields(): array
    {
        return [
            "АКБ_Новый" => "battery_is_new",
            "ДатаОкончанияГарантии" => "warranty_end_date",
            "КодТовара" => "product_code",
            "КодУцененногоТовара" => "discounted_product_code",
            "КомментарийИнженера" => "engineer_comment",
            "Комплект" => "kit",
            "Комплектация_Адаптер" => "equipment_adapter",
            "Комплектация_Кабель" => "equipment_cable",
            "Комплектация_Коробка" => "contents_box",
            "Комплектация_Ремешок" => "equipment_strap",
            "ПричинаУценкиРазвернуто" => "discount_reason_extended",
            "Работоспособность" => "operability",
            "Регион" => "region",
            "СерийныйНомер" => "serial_number",
            "Склад" => "warehouse",
            "СкладНаименование" => "warehouse_name",
            "Состояние" => "condition",
            "Цена" => "price",
            "ЭтоТрейдИн" => "is_trade_in",
        ];
    }

    public static function isRegisteredField(string $russianFieldName, LoggerInterface $logger): bool
    {
        $registeredColumnsKeys = array_keys(self::getRegisteredFields());

        if (in_array($russianFieldName, $registeredColumnsKeys, true)) {
            return true;
        }

        $errorMessage = "Detected unregistered product russian field name: $russianFieldName";

        $logger->error($errorMessage);

        return false;
    }

    public static function getEnglishFieldName($russianFieldName): string
    {
        $registeredFields = self::getRegisteredFields();

        return $registeredFields[$russianFieldName] ?? '';
    }

    public static function getWebData(string $productCode, Client $client, LoggerInterface $logger): array
    {
        try {
            $response = $client->get("https://stage.api.iport.ru/api/products/$productCode");

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            $logger->error($exception->getMessage());

            return [];
        } catch (GuzzleException $exception) {
            $logger->error($exception->getMessage());
            return [];
        }
    }
}
