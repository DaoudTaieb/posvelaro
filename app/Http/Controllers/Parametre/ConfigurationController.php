<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function general()
    {
        $configs = \Illuminate\Support\Facades\DB::table('retailconfigs')
            ->where('pagelibelle', 'Général')
            ->orderBy('ordreaffichage')
            ->get();

        return view('parametre.configuration.general', compact('configs'));
    }

    public function updateGeneral(Request $request)
    {
        $configsData = $request->input('config', []);
        
        foreach ($configsData as $id => $value) {
            \Illuminate\Support\Facades\DB::table('retailconfigs')
                ->where('retailconfigid', $id)
                ->update(['value' => $value]);
        }

        return redirect()->route('parametre.configuration.general')->with('success', 'Configuration mise à jour avec succès.');
    }
}
