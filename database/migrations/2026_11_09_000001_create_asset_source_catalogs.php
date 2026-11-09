<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['asset_origins', 'incorporation_methods'] as $name) {
            Schema::create($name, function (Blueprint $t) {
                $t->id();
                $t->string('code', 100)->unique();
                $t->string('name')->unique();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('incorporation_methods');
        Schema::dropIfExists('asset_origins');
    }
};
