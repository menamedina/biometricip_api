@extends('layouts.admin')

@section('title', 'Acceso denegado')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="text-center">
        <div class="mb-4" style="line-height:1;">
            <span style="font-size: 7rem; font-weight: 800; color: #e9ecef; letter-spacing: -4px;">403</span>
        </div>
        <div class="mb-3">
            <i class="ti ti-shield-lock" style="font-size: 3.5rem; color: #1ab394;"></i>
        </div>
        <h3 class="fw-bold mb-2">Acceso denegado</h3>
        <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
            No tienes permiso para acceder a esta sección.<br>
            Contacta al administrador si crees que esto es un error.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <i class="ti ti-home me-1"></i> Volver al inicio
        </a>
    </div>
</div>
@endsection
