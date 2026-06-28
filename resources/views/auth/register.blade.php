<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Laravel 13</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card bg-base-100 w-full max-w-md shadow-2xl p-6 sm:p-8">
        <div class="card-body p-0">
            <h2 class="card-title text-2xl font-bold mb-2">Buat Akun Baru</h2>
            <p class="text-sm text-base-content/70 mb-6">Silakan isi formulir di bawah untuk mendaftar.</p>

            <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
                @csrf <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Nama Lengkap</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" 
                        class="input input-bordered w-full @error('name') input-error @enderror" required autofocus />
                    @error('name')
                        <span class="text-xs text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" 
                        class="input input-bordered w-full @error('email') input-error @enderror" required />
                    @error('email')
                        <span class="text-xs text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Password</span>
                    </label>
                    <input type="password" name="password" placeholder="••••••••" 
                        class="input input-bordered w-full @error('password') input-error @enderror" required />
                    @error('password')
                        <span class="text-xs text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Konfirmasi Password</span>
                    </label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" 
                        class="input input-bordered w-full" required />
                </div>

                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full text-white font-semibold">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="divider my-6">atau</div>

            <p class="text-center text-sm">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="link link-primary font-semibold">Login di sini</a>
            </p>
        </div>
    </div>

</body>
</html>