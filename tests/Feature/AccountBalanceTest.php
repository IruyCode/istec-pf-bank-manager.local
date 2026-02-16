<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BankManager\Models\AccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test; 

class AccountBalanceTest extends TestCase
{
    use RefreshDatabase;

    //TESTE ADICIONAR NOVA CONTA BANCÁRIA 
    #[Test]
    public function an_authenticated_user_can_store_an_account_balance()
    {
        //Simular a criação de um utilizador
        $user = User::factory()->create();

        //Dados a ser enviados para o teste
        $data = [
            'account_name' => 'Conta Corrente Principal',
            'bank_name' => 'Banco do Brasil',
            'current_balance' => 1500.50,
            'account_type' => 'personal',
        ];

        $response = $this->actingAs($user)->post(route('bank-manager.account-balances.store'), $data);

        $response->assertRedirect(route('bank-manager.account-balances.index'));
        
        //Verificação dos dados recebidos
        $this->assertDatabaseHas('app_bank_manager_account_balances', [
            'user_id' => $user->id,
            'account_name' => 'Conta Corrente Principal',
            'bank_name' => 'Banco do Brasil',
            'current_balance' => 1500.50,
            'account_type' => 'personal',
        ]);
    }

    #[Test] //TESTE DE VALIDAÇÃO DOS CAMPOS 
    public function it_fails_validation_when_fields_are_missing()
    {
        //Simular a criação de um utilizador
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('bank-manager.account-balances.store'), []);

        //Verificar se os campos estão todos preenchidos
        $response->assertSessionHasErrors(['account_name', 'bank_name', 'current_balance', 'account_type']);
        
        $this->assertEquals(0, AccountBalance::count());
    }

    //TESTE EDITAR CONTA BANCÁRIA 
    #[Test]
    public function an_authenticated_user_can_update_their_own_account_balance()
    {
        //Simular a criação de um utilizador
        $user = User::factory()->create();
        
        //Simular a criação de uma conta para o utilizador
        $account = AccountBalance::create([
            'user_id' => $user->id,
            'account_name' => 'Nome Antigo',
            'bank_name' => 'Banco Antigo',
            'current_balance' => 100.00,
            'account_type' => 'personal',
            'is_active' => true,
        ]);

        //Edição dos dados da conta
        $updatedData = [
            'account_name' => 'Nome Atualizado',
            'bank_name' => 'Novo Banco',
            'current_balance' => 500.00,
            'account_type' => 'business',
            'is_active' => false,
        ];

        $response = $this->actingAs($user)
            ->put(route('bank-manager.account-balances.update', $account->id), $updatedData);

        $response->assertRedirect(route('bank-manager.account-balances.index'));
        $response->assertSessionHas('success', 'Conta bancária atualizada com sucesso!');

        //Verificação se os dados foram realmente editados
        $this->assertDatabaseHas('app_bank_manager_account_balances', [
            'id' => $account->id,
            'account_name' => 'Nome Atualizado',
            'current_balance' => 500.00,
            'is_active' => 0, 
        ]);
    }

    #[Test] //TESTE DE SEGURANÇA SE OUTRO UTILIZADOR CONSEGUE ALTERAR OS DADOS DE OUTRO UTILIZADOR
    public function a_user_cannot_update_another_users_account()
    {
        //Simular a criação de 2 utilizadores
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        
        //Conta do utilizador B
        $accountOfUserB = AccountBalance::create([
            'user_id' => $userB->id,
            'account_name' => 'Conta do B',
            'bank_name' => 'Banco B',
            'current_balance' => 10.00,
            'account_type' => 'personal',
            'is_active' => true,
        ]);

        //Utilizador A a tentar editar os dados da conta do utilizador B
        $response = $this->actingAs($userA)
            ->put(route('bank-manager.account-balances.update', $accountOfUserB->id), [
                'account_name' => 'Conta do A',
                'bank_name' => 'Banco A',
                'current_balance' => 9999,
                'account_type' => 'business',
                'is_active' => true,
            ]);

        //Erro 404, o utilizador A não encontrará a conta de B
        $response->assertStatus(404);

        //Confirma que os dados originais na base de dados não mudaram
        $this->assertDatabaseHas('app_bank_manager_account_balances', [
            'id' => $accountOfUserB->id,
            'account_name' => 'Conta do B'
        ]);
    }

    //TESTE APAGAR CONTA BANCÁRIA
    #[Test]
    public function an_authenticated_user_can_delete_their_own_account_balance()
    {
        //Simular a criação de um utilizador
        $user = User::factory()->create();
        
        //Simular a criação de uma conta para o utilizador
        $account = AccountBalance::create([
            'user_id' => $user->id,
            'account_name' => 'Conta para Eliminar',
            'bank_name' => 'Banco Teste',
            'current_balance' => 0,
            'account_type' => 'personal',
            'is_active' => true,
        ]);

        //Verificação para saber se a conta realmente existe
        $this->assertDatabaseHas('app_bank_manager_account_balances', ['id' => $account->id]);

        $response = $this->actingAs($user)
            ->delete(route('bank-manager.account-balances.delete', $account->id));

        $response->assertRedirect(route('bank-manager.account-balances.index'));
        $response->assertSessionHas('success', 'Conta bancária eliminada com sucesso!');

        //Verificar se o registo foi removido
        $this->assertDatabaseMissing('app_bank_manager_account_balances', ['id' => $account->id]);
    }

    #[Test] //TESTE PARA VERIFICAR SE UM UTILIZADOR CONSEGUE APAGAR A CONTA DE OUTRO
    public function a_user_cannot_delete_another_users_account()
    {
        //Simular a criação de 2 utilizadores
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        
        //Simular a criação de uma conta para o utilizador B
        $accountOfUserB = AccountBalance::create([
            'user_id' => $userB->id,
            'account_name' => 'Conta Intocável',
            'bank_name' => 'Banco B',
            'current_balance' => 100,
            'account_type' => 'business',
            'is_active' => true,
        ]);

        //Utilizador A tenta apagar a conta do utilizador B
        $response = $this->actingAs($userA)
            ->delete(route('bank-manager.account-balances.delete', $accountOfUserB->id));

        //Erro 404 esperado porque o utilizador A não irá encontrar a conta de B
        $response->assertStatus(404);

        //Confirmação que a conta de B ainda existe
        $this->assertDatabaseHas('app_bank_manager_account_balances', ['id' => $accountOfUserB->id]);
    }
}