<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    /**
     * Type de valeur booléenne dans retailconfigs.typevalueid (rendu en interrupteur dans la vue).
     */
    private const TYPE_BOOLEAN = 2;

    public function general()
    {
        $configs = DB::table('retailconfigs')
            ->where('pagelibelle', 'Général')
            ->orderBy('ordreaffichage')
            ->get();

        return view('parametre.configuration.general', compact('configs'));
    }

    public function updateGeneral(Request $request)
    {
        $configsData = $request->input('config', []);
        if (!is_array($configsData)) {
            $configsData = [];
        }

        // Seules les configurations de la page "Général" peuvent être modifiées ici
        $configs = DB::table('retailconfigs')
            ->where('pagelibelle', 'Général')
            ->get(['retailconfigid', 'typevalueid', 'value'])
            ->keyBy('retailconfigid');

        DB::transaction(function () use ($configsData, $configs) {
            foreach ($configsData as $id => $value) {
                if (!$configs->has($id) || !(is_scalar($value) || $value === null)) {
                    continue;
                }

                $actuelle = (string) $configs[$id]->value;
                $estBooleen = (int) $configs[$id]->typevalueid === self::TYPE_BOOLEAN
                    && in_array(strtolower(trim($actuelle)), ['true', 'false'], true);

                if ($estBooleen) {
                    $vrai = in_array(strtolower(trim((string) $value)), ['true', '1', 'on', 'oui'], true);
                    // Garder l'écriture existante ("false" / "False") lue par le logiciel de bureau
                    $value = $vrai ? 'True' : 'False';
                    if ($actuelle === strtolower($actuelle)) {
                        $value = strtolower($value);
                    }
                } else {
                    $value = (string) ($value ?? '');
                }

                if ($value === $actuelle) {
                    continue;
                }

                DB::table('retailconfigs')
                    ->where('retailconfigid', $id)
                    ->where('pagelibelle', 'Général')
                    ->update(['value' => $value]);
            }
        });

        return redirect()->route('parametre.configuration.general')->with('success', 'Configuration mise à jour avec succès.');
    }
}
