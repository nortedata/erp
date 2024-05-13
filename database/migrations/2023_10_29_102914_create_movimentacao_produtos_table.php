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
        Schema::create('movimentacao_produtos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->decimal('quantidade', 14, 4);
            $table->enum('tipo', ['incremento', 'reducao']);
            $table->integer('codigo_transacao');
            $table->enum('tipo_transacao', ['venda_nfe', 'venda_nfce', 'compra', 'alteracao_estoque']);
            $table->foreignId('produto_variacao_id')->nullable()->constrained('produto_variacaos');
            // alter table movimentacao_produtos add column produto_variacao_id integer default null;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacao_produtos');
    }
};
