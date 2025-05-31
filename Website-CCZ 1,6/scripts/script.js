document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const messageDiv = document.getElementById('message');

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário (recarregar a página)

        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const username = usernameInput.value;
        const password = passwordInput.value;

        // Simulação de verificação de login
        if (username === 'usuario' && password === 'senha') {
            messageDiv.textContent = 'Login bem-sucedido!';
            messageDiv.className = 'success'; // Adicione uma classe de sucesso para estilizar a mensagem
            // Aqui você faria o redirecionamento para a próxima página, por exemplo:
            // window.location.href = 'pagina-principal.html';
        } else {
            messageDiv.textContent = 'Nome de usuário ou senha incorretos.';
            messageDiv.className = 'error';
        }
    });
});


        function formatarCPF(campo) {
            let cpf = campo.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            if (cpf.length > 11) {
                cpf = cpf.substring(0, 11); // Limita a 11 dígitos se houver mais
            }

            // Adiciona a formatação
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

            campo.value = cpf;
        }


        
        function formatarRG(campo) {
            let rg = campo.value.replace(/\D/g, '');
            // Exemplo de formatação de RG: xx.xxx.xxx-x
            
            if (rg.length > 9) { 
                rg = rg.substring(0, 9);
            }
            rg = rg.replace(/(\d{2})(\d)/, '$1.$2');
            rg = rg.replace(/(\d{3})(\d)/, '$1.$2');
            rg = rg.replace(/(\d{3})(\d{1})$/, '$1-$2');
            campo.value = rg;
        }


         function formatarTelefone(campo) {
            let tel = campo.value.replace(/\D/g, '');
            // Exemplo de formatação de Telefone: (xx) xxxxx-xxxx ou (xx) xxxx-xxxx
          
            if (tel.length > 11) { // 11 dígitos para celular com DDD
                tel = tel.substring(0, 11);
            }
            if (tel.length > 10) { // Celular 9XXXX-XXXX
                tel = tel.replace(/^(\d\d)(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (tel.length > 6) { // Fixo XXXX-XXXX
                tel = tel.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (tel.length > 2) { // (XX) XXXX
                tel = tel.replace(/^(\d\d)(\d{0,5})/, '($1) $2');
            }
            campo.value = tel;
        }

        // Atribua as funções de formatação aos outros campos se mudou para type="text"
        document.getElementById('rg').onkeyup = function() { formatarRG(this); };
