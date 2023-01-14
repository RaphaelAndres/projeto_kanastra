<?php

namespace Tests\Feature;

use App\Exceptions\DocumentDuplicatedInvoiceException;
use App\Exceptions\InvalidCustomerDataException;
use App\Exceptions\InvalidInvoiceDataException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Repositories\InvoiceUploadRepository;
use ReflectionClass;
use Tests\TestCase;
use Illuminate\Support\Collection;

class InvoiceUploadTest extends TestCase
{

    public function test_processCustomersFromInvoiceUpload_shouldSucceed() {
        $spradsheet_data = [[
            ['name','document','email'],
            $first_customer = ['joão da silva', '01234567890', 'joaosilva@gmail.com'],
            $second_customer = ['joão do cpf', '01234567891', 'joaosilvacpf@gmail.com'],
            $third_customer = ['joão da silva cnpj', '12345678910123', 'joaosilvcnpja@gmail.com'],
        ]];
        $invoice_upload_repository = new InvoiceUploadRepository();

        $reflection = new ReflectionClass(InvoiceUploadRepository::class);
        $method = $reflection->getMethod('processCustomersFromInvoiceUpload');
        $method->setAccessible(true);
        $customers = $method->invokeArgs($invoice_upload_repository, $spradsheet_data);

        $this->assertCustomerData($customers[0], $first_customer);
        $this->assertCustomerData($customers[1], $second_customer);
        $this->assertCustomerData($customers[2], $third_customer);
    }

    public function test_processCustomersFromInvoiceUpload_shouldFail() {
        $spradsheet_data = [[
            ['name','document','email'],
            ['', '01234567890', 'joaosilva@gmail.com'],
            ['joão da silva', '', 'joaosilva@gmail.com'],
            ['joão da silva', '01234567890', ''],
            ['joão da silva', '01234567890', 'joaosilvagmail.com'],
            ['joão da silva', '101234567890', 'joaosilva@gmail.com'],
        ]];

        $invoice_upload_repository = new InvoiceUploadRepository();

        $reflection = new ReflectionClass(InvoiceUploadRepository::class);
        $method = $reflection->getMethod('processCustomersFromInvoiceUpload');
        $method->setAccessible(true);
        
        $this->expectException(InvalidCustomerDataException::class);
        $this->expectExceptionMessage('Invalid data given for costumer(s) in line(s): 2, 3, 4');

        $method->invokeArgs($invoice_upload_repository, $spradsheet_data);
    }

    public function test_processInvoicesFromInvoiceUpload_shouldSucceed() {
        $spradsheet_data = [
            ['name','document','email','debtAmount','debtDueDate','debtId'],
            $first_invoice = ['joão da silva', '01234567890', 'joaosilva@gmail.com',1.00,'2023-10-12',1000],
            $second_invoice = ['joão do cpf', '01234567891', 'joaosilvacpf@gmail.com',1.00,'2023-10-12',1001],
            $third_invoice = ['joão da silva cnpj', '12345678910123', 'joaosilvcnpja@gmail.com',1.00,'2023-10-12',1002],
        ];
        
        $customers = new Collection();
        
        $customers->add($this->mockCustomer('joão da silva', '01234567890', 'joaosilva@gmail.com'));
        $customers->add($this->mockCustomer('joão do cpf', '01234567891', 'joaosilvacpf@gmail.com'));
        $customers->add($this->mockCustomer('joão da silva cnpj', '12345678910123', 'joaosilvcnpja@gmail.com'));

        $invoice_upload_repository = new InvoiceUploadRepository();

        $reflection = new ReflectionClass(InvoiceUploadRepository::class);
        $method = $reflection->getMethod('processInvoicesFromInvoiceUpload');
        $method->setAccessible(true);
        $invoice = $method->invokeArgs($invoice_upload_repository, [$spradsheet_data, $customers]);

        $this->assertInvoiceData($first_invoice);
        $this->assertInvoiceData($second_invoice);
        $this->assertInvoiceData($third_invoice);
    }

    /** @dataProvider getFailScenariosForInvoiceUpload */
    public function test_processInvoicesFromInvoiceUpload_shouldFail($spradsheet_data, $exception_type, $exception_message) {
        $customers = new Collection();
        
        $invoice_upload_repository = new InvoiceUploadRepository();

        $reflection = new ReflectionClass(InvoiceUploadRepository::class);
        $method = $reflection->getMethod('processInvoicesFromInvoiceUpload');
        $method->setAccessible(true);
        
        $this->expectException($exception_type);
        $this->expectExceptionMessage($exception_message);

        $invoice = $method->invokeArgs($invoice_upload_repository, [$spradsheet_data, $customers]);
    }

    public function getFailScenariosForInvoiceUpload() {
        return [
            [
                [
                    ['name','document','email','debtAmount','debtDueDate','debtId'],
                    ['joão da silva', '01234567890', 'joaosilva@gmail.com','','2023-10-12',1000],
                    ['joão do cpf', '01234567891', 'joaosilvacpf@gmail.com',1.00,'',1001],
                    ['joão da silva cnpj', '12345678910123', 'joaosilvcnpja@gmail.com',1.00,'2023-10-12',''],
                    ['joão da silva', '01234567890', 'joaosilva@gmail.com',-1,'2023-10-12',1000],
                    ['joão do cpf', '01234567891', 'joaosilvacpf@gmail.com',1.00,'2022-10-12',1001],
                ],
                InvalidInvoiceDataException::class,
                'Invalid data given for invoice(s) in line(s): 2, 3, 4, 5, 6',
            ],
            [
                [
                    ['name','document','email','debtAmount','debtDueDate','debtId'],
                    ['joão da silva', '01234567890', 'joaosilva@gmail.com',1.00,'2023-10-12',1000],
                    ['joão do cpf', '01234567891', 'joaosilvacpf@gmail.com',1.00,'2023-10-12',1000],
                    ['joão da silva cnpj', '12345678910123', 'joaosilvcnpja@gmail.com',1.00,'2023-10-12',1000],
                ],
                DocumentDuplicatedInvoiceException::class,
                'Debt ID duplicated in the following pair(s) of line(s): [2, 3], [2, 4], [3, 2], [3, 4], [4, 2], [4, 3]',
            ],
        ];
    }

    private function assertCustomerData($customer, array $customer_data) {
        $this->assertEquals($customer->name, $customer_data[0]);
        $this->assertEquals($customer->document, $customer_data[1]);
        $this->assertEquals($customer->email, $customer_data[2]);
    }

    private function mockCustomer($name, $document, $email) {
        $customer = new Customer(['name' => $name, 'document' => $document, 'email' => $email]);
        $customer->save();
        return $customer;
    }

    private function assertInvoiceData(array $invoice_data) {
        $db_invoice = Invoice::where('debt_id', $invoice_data[5])->first();

        $this->assertNotNull($db_invoice);

        $this->assertEquals($db_invoice->initial_debt_amount, $invoice_data[3]);
        $this->assertEquals($db_invoice->debt_amount, $invoice_data[3]);
        $this->assertEquals($db_invoice->debt_due_date->format('Y-m-d'), $invoice_data[4]);
        $this->assertEquals($db_invoice->debt_id, $invoice_data[5]);
        $this->assertFalse($db_invoice->paid);
    }
}
