<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventori Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #134e4a;
            --bg-2: #0f766e;
            --bg-3: #f59e0b;
            --panel: #f8fafc;
            --brand: #0f766e;
            --brand-dark: #115e59;
            --text: #0f172a;
        }

        * {
            font-family: "Plus Jakarta Sans", sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(140deg, var(--bg-1), var(--bg-2) 55%, var(--bg-3));
            overflow-x: hidden;
        }

        .blob {
            position: fixed;
            border-radius: 9999px;
            filter: blur(2px);
            opacity: 0.28;
            animation: floatBlob 9s ease-in-out infinite;
            pointer-events: none;
        }

        .blob-1 {
            width: 250px;
            height: 250px;
            background: #fbbf24;
            top: -70px;
            left: -60px;
        }

        .blob-2 {
            width: 320px;
            height: 320px;
            background: #34d399;
            bottom: -110px;
            right: -80px;
            animation-delay: 1.5s;
        }

        .blob-3 {
            width: 190px;
            height: 190px;
            background: #22d3ee;
            top: 45%;
            right: 8%;
            animation-delay: 3s;
        }

        @keyframes floatBlob {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-16px) translateX(8px); }
        }

        .error-shake {
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
        }

        .btn-interactive {
            position: relative;
            overflow: hidden;
            transition: transform 160ms ease, box-shadow 200ms ease, filter 200ms ease;
        }

        .btn-interactive::before {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 80%;
            height: 100%;
            background: linear-gradient(105deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: left 380ms ease;
            pointer-events: none;
        }

        .btn-interactive:hover::before {
            left: 130%;
        }

        .btn-interactive:focus-visible {
            outline: 3px solid rgba(13, 148, 136, 0.35);
            outline-offset: 2px;
        }

        .btn-interactive:active {
            transform: translateY(1px) scale(0.995);
        }

        .icon-btn {
            transition: transform 140ms ease, background-color 160ms ease, color 160ms ease;
        }

        .icon-btn:hover {
            transform: scale(1.06);
        }

        .icon-btn:focus-visible {
            outline: 3px solid rgba(13, 148, 136, 0.35);
            outline-offset: 2px;
        }
    </style>
</head>
<body class="relative">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center p-4 md:p-6">
        <div class="w-full max-w-5xl rounded-3xl overflow-hidden shadow-2xl border border-white/20 bg-white/90 backdrop-blur-md">
            <div class="grid lg:grid-cols-2">
                <section class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-teal-700 to-teal-900 text-white">
                    <div>
                        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-white/15 border border-white/20 mb-8">
                            <i class="fas fa-cubes-stacked"></i>
                            <span class="text-sm font-semibold tracking-wide">Sistem Inventori UD. Bersaudara</span>
                        </div>
                        <h1 class="text-4xl font-extrabold leading-tight">
                            Kelola Barang Masuk dan Keluar dalam Satu Alur
                        </h1>
                        <p class="mt-5 text-teal-100 leading-relaxed">
                            Aplikasi ini menghubungkan data stok, pembelian, dan penjualan agar tim user dan admin bekerja lebih sinkron.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-check mt-1 text-emerald-300"></i>
                            <p class="text-sm text-teal-100">Stok otomatis berubah saat transaksi barang masuk/keluar.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-check mt-1 text-emerald-300"></i>
                            <p class="text-sm text-teal-100">Role admin dan user terpisah untuk keamanan sistem.</p>
                        </div>
                    </div>
                </section>

                <section class="p-6 sm:p-8 md:p-10 bg-[var(--panel)]">
                    <div class="max-w-md mx-auto">
                        <div class="mb-8">
                            <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-teal-600 text-white shadow-lg mb-4">
                                <i class="fas fa-store text-xl"></i>
                            </div>
                            <h2 class="text-3xl font-extrabold text-[var(--text)]">Masuk Akun</h2>
                            <p class="mt-2 text-sm text-slate-500">Masukkan username dan password untuk melanjutkan.</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="error-shake mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 flex items-start gap-3">
                                <i class="fas fa-triangle-exclamation mt-0.5"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" method="POST" class="space-y-5">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div>
                                <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                                <div class="relative group">
                                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-600"></i>
                                    <input
                                        class="w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 py-3 text-slate-800 placeholder-slate-400 outline-none focus:ring-4 focus:ring-teal-100 focus:border-teal-500 transition"
                                        type="text"
                                        id="username"
                                        name="username"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Masukkan username"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                                <div class="relative group">
                                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-600"></i>
                                    <input
                                        class="w-full rounded-xl border border-slate-300 bg-white pl-11 pr-12 py-3 text-slate-800 placeholder-slate-400 outline-none focus:ring-4 focus:ring-teal-100 focus:border-teal-500 transition"
                                        type="password"
                                        id="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Masukkan password"
                                    >
                                    <button
                                        type="button"
                                        id="togglePassword"
                                        class="icon-btn absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-lg text-slate-500 hover:text-teal-700 hover:bg-teal-50"
                                        aria-label="Tampilkan atau sembunyikan password"
                                        aria-pressed="false"
                                    >
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <p id="capsLockWarning" class="hidden mt-2 text-xs text-amber-700 font-semibold">
                                    <i class="fas fa-keyboard mr-1"></i>Caps Lock sedang aktif
                                </p>
                            </div>

                            <button
                                id="submitBtn"
                                class="btn-interactive w-full rounded-xl bg-[var(--brand)] hover:bg-[var(--brand-dark)] text-white font-bold py-3.5 px-4 shadow-lg shadow-teal-700/20 flex items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                type="submit"
                                disabled
                            >
                                <i class="fas fa-right-to-bracket" id="submitIcon"></i>
                                <span id="submitText">Masuk Sekarang</span>
                            </button>
                            <p class="text-xs text-slate-500 text-center">Tombol aktif setelah username dan password terisi.</p>
                        </form>

                        <div class="mt-7 pt-5 border-t border-slate-200 text-xs text-slate-500 flex items-center justify-between gap-3">
                            <span><i class="fas fa-shield-halved mr-1"></i>Akses aman berbasis peran</span>
                            <span id="clockText"></span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon');
        const capsLockWarning = document.getElementById('capsLockWarning');
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitIcon = document.getElementById('submitIcon');
        const clockText = document.getElementById('clockText');
        const usernameInput = document.getElementById('username');

        function updateClock() {
            const now = new Date();
            clockText.textContent = now.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function togglePassword() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
                togglePasswordBtn.setAttribute('aria-pressed', 'true');
                togglePasswordBtn.setAttribute('aria-label', 'Sembunyikan password');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
                togglePasswordBtn.setAttribute('aria-pressed', 'false');
                togglePasswordBtn.setAttribute('aria-label', 'Tampilkan password');
            }
        }

        function updateSubmitState() {
            const canSubmit = usernameInput.value.trim() !== '' && passwordInput.value.trim() !== '';
            submitBtn.disabled = !canSubmit;
        }

        function handleCapsLock(event) {
            if (event.getModifierState && event.getModifierState('CapsLock')) {
                capsLockWarning.classList.remove('hidden');
            } else {
                capsLockWarning.classList.add('hidden');
            }
        }

        togglePasswordBtn.addEventListener('click', togglePassword);
        passwordInput.addEventListener('keyup', handleCapsLock);
        passwordInput.addEventListener('keydown', handleCapsLock);
        passwordInput.addEventListener('blur', () => capsLockWarning.classList.add('hidden'));
        usernameInput.addEventListener('input', updateSubmitState);
        passwordInput.addEventListener('input', updateSubmitState);

        loginForm.addEventListener('submit', () => {
            updateSubmitState();
            if (submitBtn.disabled) {
                return;
            }
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-90', 'cursor-not-allowed');
            submitIcon.classList.remove('fa-right-to-bracket');
            submitIcon.classList.add('fa-spinner', 'fa-spin');
            submitText.textContent = 'Memproses...';
        });

        updateClock();
        updateSubmitState();
        setInterval(updateClock, 30000);
        document.getElementById('username').focus();
    </script>
</body>
</html>
