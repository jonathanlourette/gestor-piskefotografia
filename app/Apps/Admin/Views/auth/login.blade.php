@extends('admin::layouts.guest')

@section('title', 'Login - Piske Memórias')

@section('content')
    <div class="text-center mb-4">
        <h2 class="h4 fw-semibold mb-1">Faça login para continuar</h2>
        <p class="text-muted mb-0">Acesse o painel administrativo</p>
    </div>

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-body-tertiary border-0 rounded-4 rounded-end-0">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email"
                       class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 rounded-start-0 @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="seu@email.com"
                       required
                       autofocus>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Senha</label>
            <div class="input-group">
                <span class="input-group-text bg-body-tertiary border-0 rounded-4 rounded-end-0">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password"
                       class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 rounded-start-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       required>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-dark btn-lg w-100 rounded-4 fw-semibold py-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
        </button>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted small mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Acesso restrito apenas para administradores
        </p>
    </div>
@endsection