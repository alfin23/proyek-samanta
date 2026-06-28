<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laravel 13 Manual</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card bg-base-100 w-full max-w-md shadow-2xl p-6 sm:p-8">
        <div class="card-body p-0">
            <h2 class="card-title text-2xl font-bold mb-2">Selamat Datang</h2>
            <p class="text-sm text-base-content/70 mb-6">Silakan masuk menggunakan akun Anda.</p>

            @if ($errors->has('email'))
                <div class="alert alert-error mb-4 py-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ $errors->first('email') }}</span>
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                @csrf <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" 
                        class="input input-bordered w-full @error('email') input-error @enderror" required autocomplete="email" autofocus />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Password</span>
                    </label>
                    <input type="password" name="password" placeholder="••••••••" 
                        class="input input-bordered w-full" required autocomplete="current-password" />
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="label-text text-sm select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full text-white font-semibold">
                        Masuk
                    </button>
                    <a href="{{ route('register') }}" class="btn btn-error w-full text-white font-semibold mt-2">Daftar</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>