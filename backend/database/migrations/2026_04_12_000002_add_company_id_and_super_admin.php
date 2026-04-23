<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('role');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->enum('role', ['super_admin', 'admin', 'employee'])->default('employee')->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('nom_categorie');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id_categorie');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id_user');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        Schema::table('alerte_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id_product');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alerte_stocks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->enum('role', ['admin', 'employee'])->default('employee')->change();
        });
    }
};
