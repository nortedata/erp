$(function () {
    // console.clear()
    setTimeout(() => {

        if ($("#step1").length && $("#step2").length && $("#step3").length && $("#step4").length && $("#step5").length && $("#step6").length){
            let toutVar = window.localStorage.getItem('tour-app-sym');
            if(!toutVar){

                var tour = new Tour(steps);
                tour.show();
                window.localStorage.setItem('tour-app-sym', true);
            }
        }
    }, 100);
});

var steps = [
{
  title: "Bem vindo!",
  content: "<p>Esse é o seu primeiro acesso, um breve tour sobre o sistema.</p>"
}, 
{
  id: "step1",
  content: "<p>Aqui temos o ambiente de emissão, plano que a empresa esta utilizando com data de expiração, endereço de IP, e opção de upgrade do plano.</p>"
},
{
  id: "step2",
  content: "<p>Altere quando quiser para o tema claro e escuro.</p>"
},
{
  id: "step3",
  content: "<p>Dados da sua conta, configuração e opção para sair.</p>"
},
{
  id: "step4",
  content: "<p>Navegue pelas telas do sistema utilizando o menu lateral.</p>"
},
{
  id: "step5",
  content: "<p>Configure sua empresa aqui, informe os dados do emitente, faça upload do certificado digital caso emita fiscal, natureza de operação e outras informações.</p>"
},
{
  id: "step6",
  content: "<p>Cadastre seus clientes, fornecedores e usuários aqui.</p>"
}, 
{
  id: "step7",
  content: "<p>Cadastre seus produtos, categorias, marcas, estoque, padrões de tributação aqui.</p>"
}, 
{
  title: "Obrigado!",
  content: "<p>Tenha uma ótima experiencia com nosso sistema.</p>"
}
];

$('#click-tour').click(() => {

    if ($("#step1").length && $("#step2").length && $("#step3").length && $("#step4").length && $("#step5").length && $("#step6").length){
        var tour = new Tour(steps);
        tour.show();
    }
})

