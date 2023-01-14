# Projeto Kanastra
 
Neste projeto, foi desenvolvido uma API para processamento de cobranças, onde são feitas geração de débitos, cobrança automatizada e registro de pagamentos.
## Autor

- [@RaphaelJesus](https://www.github.com/raphaelAndres)


## Instalação

Requisitos para instalação:
 - PHP 8 ou superior
 - Node 18.13 (testei na versão 14.10 mas não funcionou)
 - MySql

Após clonar o projeto para a sua máquina, para gerar as dependências do projeto e do Laravel, rode em um terminal local:
```bash
  composer install
  npm i
  php artisan key:generate
```

Após isso, é necessário gerar os dados de Environment, copie o arquivo `.env.example` para `.env` e, se necessário, ajuste os campos de:
 - Endereço e porta do MySql
 - Nome do Banco de Dados
 - Usuário e senha do Banco de Dados
Com a configuração do BD realizada, rode `php artisan migrate` para criar as tabelas e colunas no BD.

Para executar o projeto localmente, execute em terminais separados:
```bash
  php artisan serve
  php artisan schedule:work
```
O primeiro comando, irá executar o servidor PHP, o segundo irá executar as tarefas agendadas (notificação de cobrança) a cada 1 minuto (sem esse comando, as tarefas agendadas não funcionam).

Agora é só acessar http://localhost:8000 e começar a usar.
## API Reference

#### Upload de CSV

```http
  POST /api/upload-invoices
```

| Parameter     | Type     | Description                                          |
| :------------ | :------- | :-------------------------                           |
| `spreadsheet` | `csv`    | Arquivo CSV contendo os dados de cobrança e clientes |

Alternativamente, também pode ser executado por interface visual em http://localhost:8000/upload-invoices

Insere registros nas tabelas `invoices` e `customers`

#### Marcar cobrança como paga

```http
  POST /api/pay-invoice
```

| Parameter    | Type     | Description                      |
| :--------    | :------- | :------------------------------- |
| `debtId`     | `int`    | Identificador do débito          |
| `paidAt`     | `string` | Data da confirmação de pagamento |
| `paidAmount` | `float`  | Valor pago                       |
| `paidBy`     | `string` | Identificação do pagador         |

Altera registros na tabela `invoices`

#### Executar cobrança manualmente

```http
  GET /api/charge-customers
```
Alternativamente, é rodado automaticamente a cada 1 minuto.
Insere registros na tabela `charge_notifications`
## Running Tests

Para executar os testes, rode o comando:

```bash
  php artisan test
```

