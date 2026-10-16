<?php

namespace App\Exceptions;

/**
 * Refus métier d'une opération de caisse (message destiné au caissier).
 * Levée par les services ; le contrôleur annule la transaction et renvoie le message tel quel.
 */
class VenteRefusee extends \RuntimeException
{
}
