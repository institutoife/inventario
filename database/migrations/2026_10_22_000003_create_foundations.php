<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->timestamps();
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $t) {
            $t->id();
            $t->foreignId('permission_id')->constrained('permissions')->restrictOnDelete();
            $t->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $t->unique(['permission_id', 'role_id']);
            $t->timestamps();
        });
        Schema::create('role_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->unique(['role_id', 'user_id']);
            $t->timestamps();
        });
        Schema::create('organizational_units', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->foreignId('parent_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('offices', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->foreignId('organizational_unit_id')->constrained('organizational_units')->restrictOnDelete();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('locations', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->foreignId('office_id')->constrained('offices')->restrictOnDelete();
            $t->boolean('is_deposit')->default(false);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('budget_items', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('asset_categories', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->foreignId('parent_id')->nullable()->constrained('asset_categories')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('physical_statuses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('administrative_statuses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('positions', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('ranks', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('people', function (Blueprint $t) {
            $t->id();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('institutional_id')->nullable()->unique();
            $t->foreignId('position_id')->nullable()->constrained('positions')->restrictOnDelete();
            $t->foreignId('rank_id')->nullable()->constrained('ranks')->restrictOnDelete();
            $t->foreignId('organizational_unit_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $t->foreignId('office_id')->nullable()->constrained('offices')->restrictOnDelete();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('assets', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->text('description');
            $t->date('registered_on');
            $t->foreignId('budget_item_id')->nullable()->constrained('budget_items')->restrictOnDelete();
            $t->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->restrictOnDelete();
            $t->foreignId('physical_status_id')->nullable()->constrained('physical_statuses')->restrictOnDelete();
            $t->foreignId('administrative_status_id')->nullable()->constrained('administrative_statuses')->restrictOnDelete();
            $t->foreignId('organizational_unit_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $t->foreignId('office_id')->nullable()->constrained('offices')->restrictOnDelete();
            $t->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->foreignId('current_person_id')->nullable()->constrained('people')->restrictOnDelete();
            $t->string('origin')->nullable();
            $t->string('incorporation_method')->nullable();
            $t->decimal('value', 18, 2)->nullable();
            $t->string('brand')->nullable();
            $t->string('model')->nullable();
            $t->string('serial_number')->nullable()->index();
            $t->text('observations')->nullable();
            $t->boolean('active')->default(true);
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->string('action')->index();
            $t->string('entity_type')->index();
            $t->unsignedBigInteger('entity_id')->nullable()->index();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->text('reason')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('active');
        });
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('people');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('administrative_statuses');
        Schema::dropIfExists('physical_statuses');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('organizational_units');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
