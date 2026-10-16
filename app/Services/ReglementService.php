<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Enregistrement des règlements client d'un ticket, au format du logiciel de bureau.
 *
 * Constaté sur les 823 règlements de la base velaro :
 *  - creglements : documentid = 0, typeid = 1, statusreglementid = 2 (Payé), montantav = -montant ;
 *  - creglementdets : une ligne par ticket réglé, documentid = cticketid, typeid = 1, même montant.
 *
 * Le trigger creglement_calc (inchangé) recalcule ctickets.acompte / netapayer à partir de
 * creglementdets. Ce format est indispensable pour que le logiciel de bureau affiche les
 * encaissements faits depuis la caisse web.
 */
class ReglementService
{
    public const TYPE_TICKET = 1;
    public const STATUT_PAYE = 2;

    /**
     * @param array<string, mixed> $donnees colonnes de creglements (clientid, siteid, montant, modereglementid,
     *                                      journalcaisseid, agencebid, date, numero, ...) ; montant > 0
     * @return int creglementid
     */
    public function enregistrerPourTicket(int $cticketid, array $donnees): int
    {
        $montant = round((float) $donnees['montant'], 3);
        $now = $donnees['date'] ?? now();

        $creglementid = (int) DB::table('creglements')->insertGetId(array_merge([
            'datecreation' => $now,
            'echeance' => $now,
            'statusreglementid' => self::STATUT_PAYE,
            'typeid' => self::TYPE_TICKET,
            'montantnet' => $montant,
            'montantav' => -$montant,
            'montantrecu' => $montant,
            'montantrs' => 0,
            'tauxrs' => 0,
            'documentavid' => 0,
            'ismultiple' => false,
            'retenuevalide' => false,
            'cours' => 1,
            'nonaffecte' => 0,
            'iscomplement' => false,
            'rendu' => 0,
            'deviseid' => 1,
        ], $donnees, [
            'date' => $now,
            'montant' => $montant,
            'documentid' => 0, // le lien au ticket est porté par creglementdets (convention du bureau)
        ]), 'creglementid');

        DB::table('creglementdets')->insert([
            'creglementid' => $creglementid,
            'montant' => $montant,
            'documentid' => $cticketid,
            'typeid' => self::TYPE_TICKET,
            'montantrs' => 0,
            'documentavid' => 0,
            'montantav' => -$montant,
            'clientid' => $donnees['clientid'],
            'journalcaisseid' => $donnees['journalcaisseid'] ?? null,
            'montantecart' => 0,
        ]);

        return $creglementid;
    }

    /**
     * Règlements d'un ticket, quel que soit leur format : bureau (creglementdets) ou ancien format
     * web (creglements.documentid = ticket, sans détail).
     */
    public function reglementsDuTicket(int $cticketid)
    {
        $anciens = DB::table('creglements')
            ->leftJoin('modereglements', 'creglements.modereglementid', '=', 'modereglements.modereglementid')
            ->where('creglements.documentid', $cticketid)
            ->where('creglements.typeid', self::TYPE_TICKET)
            ->where('creglements.statusreglementid', '!=', 4)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('creglementdets as d')->whereColumn('d.creglementid', 'creglements.creglementid'))
            ->select('creglements.creglementid', 'creglements.montant', 'creglements.date', 'creglements.modereglementid', 'modereglements.libelle as mode_libelle');

        return DB::table('creglementdets')
            ->join('creglements', 'creglementdets.creglementid', '=', 'creglements.creglementid')
            ->leftJoin('modereglements', 'creglements.modereglementid', '=', 'modereglements.modereglementid')
            ->where('creglementdets.documentid', $cticketid)
            ->where('creglementdets.typeid', self::TYPE_TICKET)
            ->where('creglements.statusreglementid', '!=', 4)
            ->select('creglements.creglementid', 'creglementdets.montant', 'creglements.date', 'creglements.modereglementid', 'modereglements.libelle as mode_libelle')
            ->unionAll($anciens)
            ->orderBy('date')
            ->get();
    }
}
