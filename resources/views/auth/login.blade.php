<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MomyButuh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Konfigurasi warna kustom untuk Tailwind CSS
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'custom-pink': {
                DEFAULT: '#F564A9',
                'light': '#FEF6FA',
                'dark': '#E9559F',
              }
            }
          }
        }
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
</head>
<body class="bg-gradient-to-br from-pink-100 via-pink-200 to-pink-300">
    <div class="relative flex items-center justify-center min-h-screen overflow-hidden">

        <div class="absolute bottom-0 left-0 w-96 h-96 bg-custom-pink/30 rounded-full -translate-y-1/2 -translate-x-1/2 blur-3xl" aria-hidden="true"></div>
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-custom-pink/30 rounded-full translate-y-1/2 translate-x-1/2 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10 w-full max-w-md px-8 py-10 space-y-6 bg-white/60 backdrop-blur-lg rounded-3xl shadow-xl">
            
            {{-- Logo --}}
            <div class="flex justify-center">
                <img src="{{ asset('images/logo.png') }}" class="h-6 me-6 sm:h-9" alt="MomyButuh Logo" />
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-800">
                Admin Login
            </h2>
            
            {{-- Form Login --}}
            <form class="space-y-6" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    {{-- Input Email --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-custom-pink/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                                <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                            </svg>                                                              
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="block w-full px-4 py-3 text-gray-900 transition duration-300 border rounded-xl pl-12 bg-custom-pink-light border-custom-pink/30 placeholder:text-custom-pink/70 focus:outline-none focus:ring-2 focus:ring-custom-pink/50 focus:border-custom-pink" 
                               placeholder="Email" value="{{ old('email') }}">
                    </div>

                    {{-- Input Password --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-custom-pink/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3A5.25 5.25 0 0 0 12 1.5Zm-3.75 5.25a3.75 3.75 0 0 1 7.5 0v3a.75.75 0 0 1-1.5 0v-3a2.25 2.25 0 0 0-4.5 0v3a.75.75 0 0 1-1.5 0v-3Z" clip-rule="evenodd" />
                            </svg>                                  
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="block w-full px-4 py-3 text-gray-900 transition duration-300 border rounded-xl pl-12 bg-custom-pink-light border-custom-pink/30 placeholder:text-custom-pink/70 focus:outline-none focus:ring-2 focus:ring-custom-pink/50 focus:border-custom-pink"
                               placeholder="Password">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            class="w-full px-4 py-3 font-semibold text-white transition-transform duration-300 transform rounded-xl bg-custom-pink hover:bg-custom-pink-dark hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-custom-pink">
                        Login
                    </button>
                </div>

                <div class="text-center">
                    <a href="#" class="text-sm text-gray-500 hover:text-custom-pink">
                        Lupa Kata Sandi?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: '{{ session('error') }}',
                position: 'center',
                confirmButtonColor: '#F564A9'
            });
        @endif
    </script>
</body>
</html>
