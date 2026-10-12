{{-- 
  ==============================================================================
  NAMA FILE      : footer.blade.php
  MODUL          : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
  KODE FITUR     : PUB-01 (Komponen Kaki Halaman / Institutional Footer Universal CAVA)
  ==============================================================================
  DESKRIPSI:
  Komponen Blade kaki halaman (Universal Footer) berdesain minimalis dan profesional.
  Ditempatkan di bagian paling bawah antarmuka pengguna (menggunakan utilitas Tailwind 'mt-auto')
  untuk menjaga estetika layout agar tidak mengambang pada layar beresolusi tinggi.
  Menyajikan logo institusi, penetapan hak cipta dinamis berbasis tahun sistem (date('Y')),
  serta tautan cepat ke regulasi institusi (Kebijakan Privasi, Syarat & Ketentuan, Pusat Bantuan).

  CARA PENGGUNAAN:
  <x-cava.footer />
  ==============================================================================
--}}

{{-- CONTAINER FOOTER: Menggunakan mt-auto untuk sticky bottom feel dalam flex container --}}
<footer class="px-6 py-5 bg-white border-t border-slate-200 mt-auto">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        
        {{-- Sisi Kiri: Logo Identitas Kampus & Hak Cipta Tahun Berjalan --}}
        <div class="flex items-center gap-3">
            {{-- Logo CAVA dengan filter grayscale agar tetap tenang dan elegan --}}
            <img src="{{ asset('assets/images/logo.webp') }}" alt="CAVA Logo" class="h-6 w-auto grayscale opacity-70">
            {{-- Tahun Hak Cipta Dicetak Otomatis Menggunakan Fungsi Bawaan PHP date('Y') --}}
            <span class="text-xs text-slate-500 font-medium">
                &copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus.
            </span>
        </div>

        {{-- Sisi Kanan: Tautan Cepat Pusat Regulasi & Layanan Bantuan --}}
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-500">
            {{-- Tautan Tab Kebijakan Privasi --}}
            <a href="{{ route('public.information', ['section' => 'kebijakan']) }}" class="hover:text-blue-900 transition font-medium">
                Kebijakan Privasi
            </a>
            
            {{-- Tautan Tab Syarat & Ketentuan Penggunaan Fasilitas --}}
            <a href="{{ route('public.information', ['section' => 'syarat-ketentuan']) }}" class="hover:text-blue-900 transition font-medium">
                Syarat & Ketentuan
            </a>
            
            {{-- Tautan Tab Pusat Bantuan Teknis & FAQ --}}
            <a href="{{ route('public.information', ['section' => 'bantuan']) }}" class="hover:text-blue-900 transition font-medium">
                Pusat Bantuan & FAQ
            </a>
        </div>
    </div>
</footer>
