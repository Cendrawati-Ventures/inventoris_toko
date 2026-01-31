<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventori Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 min-h-screen">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-sm p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-md transform transition-all hover:scale-[1.01]">
            <!-- Logo/Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-full p-5 shadow-lg">
                    <i class="fas fa-store text-white text-4xl"></i>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-center mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Sistem Inventori Toko
            </h1>
            <p class="text-center text-gray-600 mb-8 text-sm">Silakan masuk untuk melanjutkan</p>
            
            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-pulse">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-5">
                <!-- Username Field -->
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="username">
                        <i class="fas fa-user text-gray-400 mr-2"></i>Username
                    </label>
                    <input
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all placeholder-gray-400"
                        type="text"
                        id="username"
                        name="username"
                        required
                        autofocus
                        placeholder="Masukkan username"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all placeholder-gray-400"
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Login Button -->
                <button
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-lg transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                    type="submit"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Masuk Sekarang</span>
                </button>
            </form>

            <!-- Security Notice -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-xs text-gray-500">
                    <i class="fas fa-lock text-gray-400 mr-1"></i>
                    Halaman ini dilindungi. Gunakan username dan password yang telah diberikan.
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto focus on username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
