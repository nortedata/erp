
<div class="modal fade" id="modal-pix" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Informe seu CPF</h5>
        
      </div>
      <form method="post" action="{{ route('food.pagamento-pix') }}" id="form-pix">
        @csrf
      <input type="hidden" name="link" value="{{ $config->loja_id }}">
        
        <div class="modal-body">
          <input type="tel" class="form-control cpf" name="cpf" id="inp-cpf" placeholder="CPF">
          <input type="hidden" class="carrinho_id" name="carrinho_id">
          <input type="hidden" class="total" name="total">
          <input type="hidden" class="tipo_pagamento" name="tipo_pagamento">
          <input type="hidden" class="observacao" name="observacao">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-main text-white" id="btn-pix">Gerar QrCode</button>
        </div>
      </form>
    </div>
  </div>
</div>