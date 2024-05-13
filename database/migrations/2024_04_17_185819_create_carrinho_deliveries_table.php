<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carrinho_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('endereco_id')->nullable()->constrained('endereco_deliveries');

            $table->enum('estado', ['pendente', 'finalizado']);
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_desconto', 10, 2);
            $table->string('cupom', 6);
            $table->string('fone', 20);
            $table->decimal('valor_frete', 10, 2);
            $table->string('session_cart_delivery', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrinho_deliveries');
    }
};
