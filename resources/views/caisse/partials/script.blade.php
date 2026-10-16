{{-- Script de caisse public/js/caisse/{{ $fichier }}.js, versionné par sa date de modification (cache navigateur) --}}
<script src="{{ asset('js/caisse/' . $fichier . '.js') }}?v={{ @filemtime(public_path('js/caisse/' . $fichier . '.js')) }}"></script>
