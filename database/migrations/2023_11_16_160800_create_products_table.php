<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->boolean('was_in_unloading')->default(false);
            $table->string("battery_is_new")->nullable();
            $table->string("condition")->nullable();
            $table->string("contents_box")->nullable();
            $table->string("discount_reason_extended")->nullable();
            $table->string("discounted_product_code")->nullable();
            $table->string("engineer_comment")->nullable();
            $table->string("equipment_adapter")->nullable();
            $table->string("equipment_cable")->nullable();
            $table->string("equipment_strap")->nullable();
            $table->string("is_trade_in")->nullable();
            $table->index("is_trade_in");
            $table->string("kit")->nullable();
            $table->string("name")->nullable();
            $table->string("operability")->nullable();
            $table->decimal("original_price", 10)->nullable();
            $table->decimal("price", 10)->nullable();
            $table->string("product_code");
            $table->index("product_code");
            $table->string("region")->nullable();
            $table->string("serial_number")->nullable();
            $table->string("warehouse")->nullable();
            $table->string("warehouse_name")->nullable();
            $table->string("warranty_end_date")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
