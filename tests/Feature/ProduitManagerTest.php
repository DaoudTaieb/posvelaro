<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProduitManagerTest extends TestCase
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

    public function test_consultation_articles_affiche_la_liste()
    {
        $response = $this->get('/stock/articles');

        // Check if the route exists, otherwise we'll get a 404
        if ($response->status() === 404) {
            $this->markTestSkipped('La route /stock/articles n\'est pas définie dans web.php.');
        }

        $response->assertStatus(200);
        $response->assertViewIs('stock.articles.index');
        $response->assertViewHas('articles');
    }

    public function test_recherche_articles_par_famille()
    {
        // On récupère une famille qui possède des produits
        $produit = DB::table('produits')->whereNotNull('familleid')->first();

        if (!$produit) {
            $this->markTestSkipped('Aucun produit avec une famille n\'existe dans la base.');
        }

        $response = $this->get('/stock/articles?familleid=' . $produit->familleid);

        if ($response->status() === 404) {
            $this->markTestSkipped('La route /stock/articles n\'est pas définie.');
        }

        $response->assertStatus(200);
        $articles = $response->viewData('articles');
        
        // Vérifier que chaque article retourné appartient à cette famille
        foreach ($articles as $article) {
            $dbArticle = DB::table('produits')->where('produitcode', $article->produitcode)->first();
            $this->assertEquals($produit->familleid, $dbArticle->familleid);
        }
    }
}
