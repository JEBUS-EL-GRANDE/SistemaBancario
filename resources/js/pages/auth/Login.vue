<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Lock, User } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="banking-login-wrapper">
        <Head title="Iniciar Sesión — Sistema Bancario" />

        <!-- Panel izquierdo decorativo -->
        <div class="banking-panel-left">
            <div class="panel-overlay" />
            <div class="panel-content">
                <div class="bank-logo">
                    <div class="logo-icon">🏦</div>
                    <span class="logo-text">BancoSistema</span>
                </div>
                <div class="panel-slogan">
                    <h2>Banca segura,<br />operaciones confiables.</h2>
                    <p>Plataforma bancaria de alta seguridad con control de acceso por roles.</p>
                </div>
                <div class="panel-features">
                    <div class="feature-item">
                        <span class="feature-icon">🔒</span>
                        <span>Seguridad RBAC</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">⚡</span>
                        <span>Transacciones ACID</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📊</span>
                        <span>Reportes en tiempo real</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho: formulario -->
        <div class="banking-panel-right">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-icon">
                        <Lock :size="24" />
                    </div>
                    <h1>Acceso al Sistema</h1>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>

                <div v-if="status" class="status-alert">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="login-form">
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Correo electrónico o usuario</label>
                        <div class="input-wrapper">
                            <User class="input-icon" :size="16" />
                            <input
                                id="email"
                                v-model="form.email"
                                type="text"
                                class="form-input"
                                placeholder="admin o correo@banco.bo"
                                required
                                autofocus
                                autocomplete="email"
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <div class="label-row">
                            <label for="password" class="form-label">Contraseña</label>
                            <a
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="forgot-link"
                            >¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="input-wrapper">
                            <Lock class="input-icon" :size="16" />
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="form-input"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Recordarme -->
                    <div class="remember-row">
                        <label class="remember-label">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="remember-check"
                            />
                            <span>Mantener sesión iniciada</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="btn-login"
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="spin" :size="18" />
                        <span v-else>Ingresar al Sistema</span>
                    </button>
                </form>

                <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.08); text-align: center; font-size: 0.85rem; color: #94a3b8;">
                    ¿Nuevo cliente bancario?
                    <a :href="route('register')" style="color: #f59e0b; font-weight: 600; text-decoration: none; margin-left: 0.25rem;">Crear acceso digital</a>
                </div>

                <p class="login-footer">
                    Sistema Bancario Modular © {{ new Date().getFullYear() }} &mdash; Acceso seguro
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* { box-sizing: border-box; }

.banking-login-wrapper {
    font-family: 'Inter', sans-serif;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 100dvh;
}

/* ── Panel izquierdo ── */
.banking-panel-left {
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f3460 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.banking-panel-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 50%, rgba(59,130,246,0.15) 0%, transparent 60%),
                radial-gradient(circle at 80% 20%, rgba(16,185,129,0.1) 0%, transparent 50%);
}

.panel-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.panel-content {
    position: relative;
    z-index: 1;
    padding: 3rem;
    color: white;
    max-width: 420px;
}

.bank-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 3rem;
}

.logo-icon { font-size: 2rem; }
.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.panel-slogan h2 {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 1rem;
    background: linear-gradient(135deg, #ffffff, #93c5fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.panel-slogan p {
    color: rgba(255,255,255,0.6);
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

.panel-features {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.75);
}

.feature-icon { font-size: 1.1rem; }

/* ── Panel derecho ── */
.banking-panel-right {
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.login-card {
    width: 100%;
    max-width: 420px;
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 4px rgba(0,0,0,0.04);
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    border-radius: 12px;
    color: white;
    margin-bottom: 1rem;
}

.login-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.375rem;
}

.login-header p {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

.status-alert {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Formulario */
.login-form { display: flex; flex-direction: column; gap: 1.25rem; }

.form-group { display: flex; flex-direction: column; gap: 0.375rem; }

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.forgot-link {
    font-size: 0.8rem;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}
.forgot-link:hover { color: #1d4ed8; }

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 0.875rem;
    color: #9ca3af;
    pointer-events: none;
}

.form-input {
    width: 100%;
    padding: 0.6875rem 0.875rem 0.6875rem 2.5rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-family: inherit;
    color: #111827;
    background: #f9fafb;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    outline: none;
}
.form-input:focus {
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

/* Recordarme */
.remember-row { margin-top: -0.25rem; }
.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #4b5563;
    user-select: none;
}
.remember-check {
    width: 16px;
    height: 16px;
    accent-color: #3b82f6;
    cursor: pointer;
}

/* Botón */
.btn-login {
    margin-top: 0.5rem;
    width: 100%;
    padding: 0.8125rem;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(59,130,246,0.35);
}
.btn-login:hover:not(:disabled) {
    opacity: 0.92;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(59,130,246,0.4);
}
.btn-login:disabled { opacity: 0.65; cursor: not-allowed; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.login-footer {
    text-align: center;
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 1.75rem;
    margin-bottom: 0;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .banking-login-wrapper { grid-template-columns: 1fr; }
    .banking-panel-left { display: none; }
    .banking-panel-right { background: white; padding: 1.5rem; align-items: flex-start; padding-top: 3rem; }
}
</style>
