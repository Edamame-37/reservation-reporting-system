<x-guest-layout>
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Login</span>
    </a>

    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Buat Akun</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">Bergabunglah untuk menikmati kemudahan manajemen fasilitas dalam genggaman.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="mb-4 p-4 bg-blue-50/50 text-[#1D1D1F] text-[13.5px] rounded-2xl border border-blue-100/50 leading-relaxed">
            <span class="font-semibold text-blue-800">Informasi Pendaftaran:</span> Akun Anda akan berstatus <em>Pending</em> dan membutuhkan verifikasi dari Admin (maks. 1x24 jam).
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Mis. Bima Dewantara">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Identifier (NIM/NIP) -->
        <div>
            <label for="identifier" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">NIM / NIP / NIDN</label>
            <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" required class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Contoh: 2108561044">
            <x-input-error :messages="$errors->get('identifier')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Kategori Sivitas -->
        <div>
            <label for="role_type" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Kategori Sivitas</label>
            <div class="relative">
                <select id="role_type" name="role_type" required class="w-full bg-[#F5F5F7] text-[#1D1D1F] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px] appearance-none cursor-pointer">
                    <option value="" disabled {{ old('role_type') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                    <option value="mahasiswa" {{ old('role_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                    <option value="dosen" {{ old('role_type') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                    <option value="staf" {{ old('role_type') == 'staf' ? 'selected' : '' }}>Staf</option>
                </select>
                <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[#86868B]">expand_more</span>
                </div>
            </div>
            <x-input-error :messages="$errors->get('role_type')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Email Kampus</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="nama@kampus.ac.id">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Bukti KTM/SK (Upload) -->
        <div>
            <label for="identity_proof" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Unggah Bukti Identitas (KTM/SK)</label>
            <div class="mt-1 flex items-center justify-center w-full" x-data="{ fileName: '' }">
                <label for="identity_proof" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer bg-[#F5F5F7] hover:bg-gray-100 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <span class="material-symbols-outlined text-[28px] text-[#86868B] mb-2">cloud_upload</span>
                        <p class="mb-1 text-[13px] text-[#86868B]" x-show="!fileName"><span class="font-semibold text-[#1D1D1F]">Klik untuk mengunggah</span> atau drag & drop</p>
                        <p class="mb-1 text-[13px] text-blue-600 font-semibold text-center px-4 break-all" x-show="fileName" x-text="fileName" style="display: none;"></p>
                        <p class="text-[12px] text-[#86868B]">PDF, JPG, atau PNG (Maks. 2MB)</p>
                    </div>
                    <input id="identity_proof" name="identity_proof" type="file" accept=".pdf,image/jpeg,image/png" class="hidden" required @change="fileName = $event.target.files[0].name" />
                </label>
            </div>
            <x-input-error :messages="$errors->get('identity_proof')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Buat sandi yang kuat">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Konfirmasi Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Ketik ulang sandi">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px]">
            Daftar & Ajukan Verifikasi
        </button>

        <div class="mt-10 pt-6 border-t border-gray-100/50 flex items-center justify-center gap-1.5 text-[14px] text-[#86868B]">
            <span>Sudah memiliki akun?</span>
            <a href="{{ route('login') }}" class="text-[#1D1D1F] font-semibold hover:underline">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>