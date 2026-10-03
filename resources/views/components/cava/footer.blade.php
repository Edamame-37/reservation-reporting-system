{{-- 
  NAMA FILE      : footer.blade.php
  FUNGSIONALITAS : Komponen Footer Global
  DESKRIPSI      : Menampilkan informasi hak cipta dan tautan relevan di bagian bawah halaman.
--}}
<footer class="px-6 py-5 bg-white border-t border-slate-200 mt-auto">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="CAVA Logo" class="h-6 w-auto grayscale opacity-70">
            <span class="text-xs text-slate-500 font-medium">&copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus.</span>
        </div>
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-500">
            <a href="{{ route('public.information', ['section' => 'kebijakan']) }}" class="hover:text-blue-900 transition font-medium">Kebijakan Privasi</a>
            <a href="{{ route('public.information', ['section' => 'syarat-ketentuan']) }}" class="hover:text-blue-900 transition font-medium">Syarat & Ketentuan</a>
            <a href="{{ route('public.information', ['section' => 'bantuan']) }}" class="hover:text-blue-900 transition font-medium">Pusat Bantuan & FAQ</a>
        </div>
    </div>
</footer>
