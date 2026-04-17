<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW get_total_financing AS
            SELECT
            financings.user_id,
            financing_products.created_at,
            financings.financing_status,
            SUM(margin_amount) + SUM(cost_price) AS total_financing
        FROM financing_products
        JOIN financings ON financing_products.id = financings.financing_product_id
        GROUP BY financings.user_id, financing_products.created_at, financings.financing_status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS get_total_financing");
    }
};
