@extends('errors.layout')
@section('code', '403')
@section('titre', 'Accès refusé')
@section('message', $exception->getMessage() ?: "Votre rôle ne vous autorise pas à accéder à cette page. Contactez un administrateur si vous pensez que c'est une erreur.")
