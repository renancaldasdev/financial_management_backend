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
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('account_type', [
                'credit_card',
                'checking',
                'savings',
                'investment',
                'loan',
            ]);

            $table->string('provider_name', 255);
            $table->string('provider_branch_code', 20)->nullable();
            $table->string('provider_branch_name', 255)->nullable();

            $table->text('account_number_encrypted');
            $table->string('account_mask', 20);

            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('due_date')->nullable();

            $table->char('currency', 3)->default('BRL');
            $table->enum('status', ['active', 'closed', 'pending'])->default('active');

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'account_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
