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
        <div class="flex gap-4 text-xs text-slate-400">
            <a href="#" class="hover:text-slate-600 transition">Kebijakan Privasi</a>
            <a href="#" class="hover:text-slate-600 transition">Syarat & Ketentuan</a>
            <a href="#" class="hover:text-slate-600 transition">Bantuan</a>
        </div>
    </div>
</footer>
