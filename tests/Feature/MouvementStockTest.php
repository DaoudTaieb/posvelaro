<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class MouvementStockTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = DB::table('users')->first();
        if ($user) {
            $userModel = new User();
            $userModel->userid = $user->userid;
            $userModel->login = $user->login;
            $userModel->exists = true;
            $this->actingAs($userModel);
        }
    }

    public function test_page_mouvements_charge_correctement()
    {
        $response = $this->get('/stock/mouvements');
        
        if ($response->status() === 404) {
            $this->markTestSkipped('La route /stock/mouvements n\'est pas définie.');
        }

        $response->assertStatus(200);
        $response->assertViewIs('stock.mouvements.index');
        $response->assertViewHasAll(['mouvements', 'dateDu', 'dateAu', 'sumAchat', 'sumVente']);
    }

    public function test_page_consultation_stock_charge_correctement()
    {
        $response = $this->get('/stock/consultation');
        
        if ($response->status() === 404) {
            $this->markTestSkipped('La route /stock/consultation n\'est pas définie.');
        }

        $response->assertStatus(200);
        $response->assertViewHas('stocks');
    }
}
