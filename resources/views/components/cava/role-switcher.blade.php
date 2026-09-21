{{-- 
  NAMA FILE      : role-switcher.blade.php
  FUNGSIONALITAS : Floating Demo Role Switcher (Pengalih Peran Mengambang)
  DESKRIPSI      : Widget mengambang di pojok kanan bawah khusus keperluan pengujian & evaluasi UI/UX tanpa merusak struktur layout asli halaman.
  CARA KERJA     : Dikelola via Alpine.js untuk ekspansi/ciut, menyediakan tombol pintas navigasi antar 4 peran pengguna.
--}}

@props([
    'activeRole' => 'public'
])

<!-- 
  ELEMEN       : Floating Demo Role Switcher Widget
  KEGUNAAN     : Mempermudah peninjauan UI/UX per peran tanpa bilah kaku di atas konten utama.
  CARA KERJA   : Melayang di pojok kanan bawah (fixed bottom-4 right-4 z-50).
-->
<div x-data="{ expanded: false }" class="fixed bottom-4 right-4 z-50">
    <div class="bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl shadow-xl p-2 flex items-center gap-1.5 transition-all text-xs font-medium text-slate-700">
        {{-- Tombol Toggle Ikon Peran --}}
        <button @click="expanded = !expanded" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-900 text-white font-semibold shadow-xs hover:bg-slate-800 transition" title="Beralih Tampilan Peran">
            <span class="material-symbols-outlined text-[16px]">switch_account</span>
            <span class="capitalize" x-text="expanded ? 'Tutup Peran' : 'Peran: {{ $activeRole }}'"></span>
        </button>

        {{-- Pilihan Peran Cepat saat Expanded --}}
        <div x-show="expanded" x-cloak class="flex items-center gap-1 pl-1">
            <a href="{{ url('/') }}" class="px-2.5 py-1.5 rounded-xl transition {{ $activeRole === 'public' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                Publik
            </a>
            <a href="{{ url('/user/dashboard') }}" class="px-2.5 py-1.5 rounded-xl transition {{ $activeRole === 'user' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                Mahasiswa
            </a>
            <a href="{{ url('/petugas/dashboard') }}" class="px-2.5 py-1.5 rounded-xl transition {{ $activeRole === 'petugas' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                Petugas
            </a>
            <a href="{{ url('/admin/dashboard') }}" class="px-2.5 py-1.5 rounded-xl transition {{ $activeRole === 'admin' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                Admin
            </a>
        </div>
    </div>
</div>
